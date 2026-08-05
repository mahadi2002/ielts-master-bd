<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class SubscriptionRepo
{
    public function find(int $id): ?array
    {
        return Db::first('SELECT * FROM subscriptions WHERE id = ?', [$id]);
    }

    public function latestForUser(int $userId): ?array
    {
        return Db::first(
            'SELECT * FROM subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1',
            [$userId]
        );
    }

    public function activeForUser(int $userId): ?array
    {
        return Db::first(
            'SELECT * FROM subscriptions
              WHERE user_id = ? AND status IN ("active","grace") AND current_period_end > NOW()
              ORDER BY id DESC LIMIT 1',
            [$userId]
        );
    }

    public function findByRef(string $subscriberRef): ?array
    {
        return Db::first('SELECT * FROM subscriptions WHERE bdapps_sub_ref = ?', [$subscriberRef]);
    }

    public function userIdFor(int $subscriptionId): ?int
    {
        $id = Db::value('SELECT user_id FROM subscriptions WHERE id = ?', [$subscriptionId]);
        return $id === null ? null : (int) $id;
    }

    public function create(int $userId, ?string $subscriberRef, float $amount): int
    {
        return Db::insert(
            'INSERT INTO subscriptions (user_id, bdapps_sub_ref, status, daily_amount)
             VALUES (?, ?, "pending", ?)',
            [$userId, $subscriberRef, $amount]
        );
    }

    /**
     * A batch of subscriptions whose period has lapsed. FOR UPDATE SKIP LOCKED
     * keeps two overlapping cron runs from charging the same row twice; on
     * MariaDB < 10.6 the idempotency key on charge_transactions is the backstop.
     */
    public function dueForCharge(int $limit = 200): array
    {
        $sql = 'SELECT s.id, s.user_id, s.bdapps_sub_ref, s.status, s.daily_amount,
                       s.current_period_end, s.grace_until
                  FROM subscriptions s
                 WHERE s.status IN ("active","grace")
                   AND s.current_period_end IS NOT NULL
                   AND s.current_period_end <= NOW()
                 ORDER BY s.current_period_end
                 LIMIT ' . (int) $limit;

        try {
            return Db::all($sql . ' FOR UPDATE SKIP LOCKED');
        } catch (\PDOException) {
            return Db::all($sql);
        }
    }

    /** Subscriptions still pending their very first charge. */
    public function pendingFirstCharge(int $limit = 200): array
    {
        return Db::all(
            'SELECT s.id, s.user_id, s.bdapps_sub_ref, s.status, s.daily_amount
               FROM subscriptions s
              WHERE s.status = "pending"
                AND s.bdapps_sub_ref IS NOT NULL
                AND s.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)
              ORDER BY s.id
              LIMIT ' . (int) $limit
        );
    }

    // ── charge ledger ───────────────────────────────────────────────────

    /**
     * Claim today's charge attempt. Returns false when another process (or an
     * earlier run of this cron) already inserted the same key — that is the
     * idempotency guarantee, enforced by a UNIQUE index, not by application logic.
     */
    public function claimCharge(int $subscriptionId, string $idempotencyKey, float $amount): bool
    {
        $affected = Db::exec(
            'INSERT IGNORE INTO charge_transactions
                (subscription_id, idempotency_key, amount, status, attempted_at)
             VALUES (?, ?, ?, "pending", NOW())',
            [$subscriptionId, $idempotencyKey, $amount]
        );

        return $affected > 0;
    }

    public function settleCharge(string $idempotencyKey, string $status, ?string $txnId, ?string $code, array $raw, ?string $failureReason = null): void
    {
        Db::exec(
            'UPDATE charge_transactions
                SET status = ?, external_txn_id = ?, status_code = ?, failure_reason = ?,
                    raw_response = ?, settled_at = NOW()
              WHERE idempotency_key = ?',
            [
                $status,
                $txnId,
                $code,
                $failureReason === null ? null : substr($failureReason, 0, 160),
                (string) json_encode($raw, JSON_UNESCAPED_UNICODE),
                $idempotencyKey,
            ]
        );
    }

    public function chargesForSubscription(int $subscriptionId, int $limit = 30): array
    {
        return Db::all(
            'SELECT amount, status, status_code, failure_reason, attempted_at, settled_at
               FROM charge_transactions
              WHERE subscription_id = ?
              ORDER BY id DESC
              LIMIT ' . (int) $limit,
            [$subscriptionId]
        );
    }

    /** @return array{success:int, failed:int, pending:int} */
    public function chargeTotalsToday(): array
    {
        $rows = Db::all(
            'SELECT status, COUNT(*) AS n FROM charge_transactions
              WHERE attempted_at >= CURDATE() GROUP BY status'
        );

        $out = ['success' => 0, 'failed' => 0, 'pending' => 0, 'reversed' => 0];
        foreach ($rows as $row) {
            $out[(string) $row['status']] = (int) $row['n'];
        }
        return $out;
    }

    public function recentFailures(int $limit = 20): array
    {
        return Db::all(
            'SELECT c.id, c.subscription_id, c.amount, c.status_code, c.failure_reason, c.attempted_at,
                    u.msisdn_last4, u.id AS user_id
               FROM charge_transactions c
               JOIN subscriptions s ON s.id = c.subscription_id
               JOIN users u ON u.id = s.user_id
              WHERE c.status = "failed"
              ORDER BY c.id DESC
              LIMIT ' . (int) $limit
        );
    }

    /** @return array<string,int> status → count */
    public function statusBreakdown(): array
    {
        $rows = Db::all('SELECT status, COUNT(*) AS n FROM subscriptions GROUP BY status');
        $out  = [];
        foreach ($rows as $row) {
            $out[(string) $row['status']] = (int) $row['n'];
        }
        return $out;
    }
}
