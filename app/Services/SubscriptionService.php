<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;
use App\Core\Logger;
use App\Core\Session;
use App\Repositories\SubscriptionRepo;

/**
 * The subscription state machine.
 *
 *   [none] --OTP verified--> pending --first charge ok--> active
 *   active --charge fail--> grace (48h, access allowed) --recharge ok--> active
 *   grace --grace expires--> expired
 *   any --STOP / operator callback--> unsubscribed
 *
 * active and grace grant access. pending, expired, unsubscribed do not.
 * Every transition writes audit_log; every transition that loses access
 * deletes the user's sessions rows.
 */
final class SubscriptionService
{
    public const ACCESS_STATES = ['active', 'grace'];

    private SubscriptionRepo $repo;

    public function __construct(?SubscriptionRepo $repo = null)
    {
        $this->repo = $repo ?? new SubscriptionRepo();
    }

    /**
     * The gate everything else in this app funnels through. Always a fresh
     * DB read — memoised per request only, so two checks in one page render
     * don't double-query, but nothing is ever carried across requests.
     */
    public static function hasAccess(int $userId): bool
    {
        static $memo = [];

        if (array_key_exists($userId, $memo)) {
            return $memo[$userId];
        }

        $sub = (new SubscriptionRepo())->latestForUser($userId);

        $ok = $sub !== null
            && in_array((string) $sub['status'], self::ACCESS_STATES, true)
            && $sub['current_period_end'] !== null
            && strtotime((string) $sub['current_period_end']) > time();

        // A row still marked active/grace but past its period end is stale —
        // the hourly cron has not caught up. Deny now, let the cron settle it.
        return $memo[$userId] = $ok;
    }

    /** The row the UI needs: status, period end, grace banner. */
    public static function current(int $userId): ?array
    {
        return (new SubscriptionRepo())->latestForUser($userId);
    }

    /** Grace users get a warning banner on every page — nudge them to recharge before access drops. */
    public static function graceNotice(int $userId): ?string
    {
        $sub = self::current($userId);
        if ($sub === null || $sub['status'] !== 'grace') {
            return null;
        }
        return 'ব্যালেন্স কম। ২ দিনের মধ্যে Recharge করুন, নাহলে Access বন্ধ হয়ে যাবে।';
    }

    // ── transitions ─────────────────────────────────────────────────────

    /**
     * Called right after OTP verification. Reuses the users row for a returning
     * subscriber and always creates a NEW subscriptions row — history is never
     * deleted, even for someone who's cancelled and come back three times.
     */
    public function startPending(int $userId, string $subscriberRef): int
    {
        $existing = $this->repo->latestForUser($userId);

        if ($existing !== null && in_array((string) $existing['status'], ['pending', 'active', 'grace'], true)) {
            // Already in flight — reuse it rather than stacking duplicate rows.
            if ((string) $existing['bdapps_sub_ref'] !== $subscriberRef) {
                Db::exec('UPDATE subscriptions SET bdapps_sub_ref = ? WHERE id = ?', [$subscriberRef, $existing['id']]);
            }
            return (int) $existing['id'];
        }

        $id = $this->repo->create($userId, $subscriberRef, (float) config('bdapps.amount', 2.78));

        AuditService::log('sub.pending', 'user', $userId, 'subscription', $id);

        return $id;
    }

    /** First successful charge, or a renewal after expiry. */
    public function activate(int $subscriptionId): void
    {
        $hours = (int) config('bdapps.period_hours', 24);

        Db::exec(
            'UPDATE subscriptions
                SET status = "active",
                    started_at = COALESCE(started_at, NOW()),
                    current_period_end = DATE_ADD(NOW(), INTERVAL ? HOUR),
                    grace_until = NULL,
                    cancelled_at = NULL,
                    cancel_source = NULL,
                    cancel_reason = NULL
              WHERE id = ?',
            [$hours, $subscriptionId]
        );

        $userId = $this->repo->userIdFor($subscriptionId);
        if ($userId !== null) {
            Db::exec('UPDATE users SET status = "active" WHERE id = ?', [$userId]);
        }

        AuditService::log('sub.activated', 'system', $userId, 'subscription', $subscriptionId);
    }

    /**
     * A successful renewal. The new period end is measured from the PREVIOUS
     * period end, not from NOW() — otherwise cron drift steals hours from the
     * user every single day. If the hourly cron runs 20 minutes late today,
     * that shouldn't be the user's problem tomorrow too.
     */
    public function renew(int $subscriptionId): void
    {
        $hours = (int) config('bdapps.period_hours', 24);

        Db::exec(
            'UPDATE subscriptions
                SET status = "active",
                    current_period_end = DATE_ADD(
                        GREATEST(COALESCE(current_period_end, NOW()), DATE_SUB(NOW(), INTERVAL 1 DAY)),
                        INTERVAL ? HOUR
                    ),
                    grace_until = NULL
              WHERE id = ?',
            [$hours, $subscriptionId]
        );

        AuditService::log('sub.renewed', 'system', $this->repo->userIdFor($subscriptionId), 'subscription', $subscriptionId);
    }

    /** A failed charge on an active subscription. Access continues for 48 h. */
    public function toGrace(int $subscriptionId, string $reason = ''): void
    {
        $hours = (int) config('bdapps.grace_hours', 48);

        Db::exec(
            'UPDATE subscriptions
                SET status = "grace",
                    grace_until = DATE_ADD(NOW(), INTERVAL ? HOUR),
                    current_period_end = DATE_ADD(NOW(), INTERVAL ? HOUR)
              WHERE id = ?',
            [$hours, $hours, $subscriptionId]
        );

        AuditService::log('sub.grace', 'system', $this->repo->userIdFor($subscriptionId), 'subscription', $subscriptionId, [
            'reason' => substr($reason, 0, 120),
        ]);
    }

    /** Grace ran out. Access is lost, so sessions are revoked. */
    public function expire(int $subscriptionId, string $reason = ''): void
    {
        Db::exec(
            'UPDATE subscriptions SET status = "expired", grace_until = NULL WHERE id = ?',
            [$subscriptionId]
        );

        $this->revokeAccess($subscriptionId, 'sub.expired', ['reason' => substr($reason, 0, 120)]);
    }

    /** User pressed Unsubscribe, sent STOP, or the operator told us. */
    public function unsubscribe(int $subscriptionId, string $source = 'user', string $reason = ''): void
    {
        $sub = $this->repo->find($subscriptionId);
        if ($sub === null) {
            return;
        }

        if ($sub['bdapps_sub_ref'] !== null) {
            try {
                GatewayFactory::make()->unsubscribe((string) $sub['bdapps_sub_ref']);
            } catch (\Throwable $e) {
                // The local state change still stands — the user asked to stop.
                Logger::warning('sub.unsubscribe.gateway_failed', ['error' => $e->getMessage()]);
            }
        }

        Db::exec(
            'UPDATE subscriptions
                SET status = "unsubscribed",
                    cancelled_at = NOW(),
                    cancel_source = ?,
                    cancel_reason = ?,
                    current_period_end = NULL,
                    grace_until = NULL
              WHERE id = ?',
            [$source, substr($reason, 0, 160) ?: null, $subscriptionId]
        );

        $this->revokeAccess($subscriptionId, 'sub.unsubscribed', ['source' => $source]);
    }

    /**
     * On losing access, every session row for that user gets deleted. An
     * already-open browser is locked out on its next request, not whenever
     * it happens to log in again.
     */
    private function revokeAccess(int $subscriptionId, string $action, array $meta = []): void
    {
        $userId = $this->repo->userIdFor($subscriptionId);

        if ($userId !== null) {
            Session::revokeAllForUser($userId);
        }

        AuditService::log($action, 'system', $userId, 'subscription', $subscriptionId, $meta);
    }
}
