<?php
declare(strict_types=1);

/**
 * Drains the `jobs` table. Runs every minute from cron.
 *
 * The only job type today is webhook.apply — applying a BDApps callback is
 * deferred here so WebhookController can respond 200 immediately; a slow
 * inline write would make BDApps retry and double-apply the event.
 *
 * Cron entry:
 *   * * * * *  /usr/local/bin/php /home/USER/ieltsmasterbd/cron/queue_worker.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Db;
use App\Core\Logger;
use App\Repositories\SubscriptionRepo;
use App\Services\AuditService;
use App\Services\SubscriptionService;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('queue_worker');
if ($release === null) {
    exit(0);
}

$processed = 0;
$deadline  = microtime(true) + 50; // stay well inside the 1-minute cron slot

while (microtime(true) < $deadline) {
    $job = Db::transaction(static function () {
        $row = Db::first(
            'SELECT id, job, payload, attempts, max_attempts FROM jobs
              WHERE queue = "default" AND reserved_at IS NULL AND available_at <= NOW()
              ORDER BY id LIMIT 1 FOR UPDATE SKIP LOCKED'
        );
        if ($row !== null) {
            Db::exec('UPDATE jobs SET reserved_at = NOW() WHERE id = ?', [$row['id']]);
        }
        return $row;
    });

    if ($job === null) {
        break;
    }

    try {
        applyJob((string) $job['job'], (array) json_decode((string) $job['payload'], true));
        Db::exec('DELETE FROM jobs WHERE id = ?', [$job['id']]);
        $processed++;
    } catch (\Throwable $e) {
        $attempts = (int) $job['attempts'] + 1;
        Logger::error('queue_worker.job_failed', ['job' => $job['job'], 'error' => $e->getMessage()]);

        if ($attempts >= (int) $job['max_attempts']) {
            Db::exec(
                'UPDATE jobs SET attempts = ?, reserved_at = NULL, failed_at = NOW(), last_error = ? WHERE id = ?',
                [$attempts, substr($e->getMessage(), 0, 500), $job['id']]
            );
        } else {
            // Exponential-ish backoff: retry in attempts^2 minutes.
            Db::exec(
                'UPDATE jobs SET attempts = ?, reserved_at = NULL, last_error = ?,
                    available_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?',
                [$attempts, substr($e->getMessage(), 0, 500), $attempts * $attempts, $job['id']]
            );
        }
    }
}

Logger::info('queue_worker.done', ['processed' => $processed]);
fwrite(STDOUT, "queue_worker: processed $processed job(s).\n");

$release();

// ── job handlers ───────────────────────────────────────────────────────

function applyJob(string $job, array $payload): void
{
    match ($job) {
        'webhook.apply' => applyWebhook((string) ($payload['event_id'] ?? '')),
        default         => throw new \RuntimeException('Unknown job type: ' . $job),
    };
}

/**
 * Interpret a stored BDApps event and drive the subscription state machine.
 * Every branch is defensive: an unrecognised or already-processed event is
 * logged and left alone rather than guessed at.
 */
function applyWebhook(string $eventId): void
{
    if ($eventId === '') {
        return;
    }

    $event = Db::first('SELECT id, type, payload, processed_at FROM webhook_events WHERE event_id = ?', [$eventId]);
    if ($event === null || $event['processed_at'] !== null) {
        return;
    }

    $payload = (array) json_decode((string) $event['payload'], true);
    $type    = strtolower((string) ($event['type'] ?? ''));

    $subscriberRef = (string) ($payload['subscriberId'] ?? $payload['subscriber_ref'] ?? '');
    $repo          = new SubscriptionRepo();
    $sub           = $subscriberRef !== '' ? $repo->findByRef($subscriberRef) : null;

    if ($sub === null) {
        Db::exec('UPDATE webhook_events SET processed_at = NOW(), error = "no matching subscription" WHERE id = ?', [$event['id']]);
        return;
    }

    $service = new SubscriptionService($repo);

    match (true) {
        str_contains($type, 'unsub') || str_contains($type, 'cancel') =>
            $service->unsubscribe((int) $sub['id'], 'operator', $type),

        str_contains($type, 'charge') && (str_contains($type, 'success') || ($payload['status'] ?? '') === 'success') =>
            $service->renew((int) $sub['id']),

        str_contains($type, 'charge') && (str_contains($type, 'fail') || ($payload['status'] ?? '') === 'failed') =>
            $service->toGrace((int) $sub['id'], $type),

        default => AuditService::log('webhook.unhandled_type', 'gateway', null, 'subscription', (int) $sub['id'], ['type' => $type]),
    };

    Db::exec('UPDATE webhook_events SET processed_at = NOW() WHERE id = ?', [$event['id']]);
}
