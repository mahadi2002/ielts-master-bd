<?php
declare(strict_types=1);

/**
 * Daily 03:00 — retention purge.
 *
 * Cron entry:
 *   0 3 * * *  /usr/local/bin/php /home/USER/ieltsmasterbd/cron/cleanup.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Db;
use App\Core\Logger;
use App\Repositories\UserRepo;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('cleanup');
if ($release === null) {
    exit(0);
}

$retention = (array) config('app.retention');
$report    = [];

$report['otp_requests'] = Db::exec(
    'DELETE FROM otp_requests WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)',
    [(int) $retention['otp_hours']]
);

$report['rate_limits'] = Db::exec(
    'DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? DAY)
       AND (blocked_until IS NULL OR blocked_until < NOW())',
    [(int) $retention['ratelimit_days']]
);

$report['webhook_events'] = Db::exec(
    'DELETE FROM webhook_events WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
    [(int) $retention['webhook_days']]
);

$report['sessions'] = Db::exec(
    'DELETE FROM sessions WHERE last_activity < ?',
    [time() - ((int) $retention['session_days'] * 86400)]
);

$report['audit_log'] = Db::exec(
    'DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
    [(int) $retention['audit_days']]
);

// Anonymise users who unsubscribed long ago and never came back.
$stale = Db::all(
    'SELECT DISTINCT u.id
       FROM users u
       JOIN subscriptions s ON s.user_id = u.id
      WHERE u.anonymized_at IS NULL
        AND s.status = "unsubscribed"
        AND s.cancelled_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        AND NOT EXISTS (
              SELECT 1 FROM subscriptions s2
               WHERE s2.user_id = u.id AND s2.id <> s.id AND s2.created_at > s.cancelled_at
        )',
    [(int) $retention['anonymize_days']]
);

$userRepo = new UserRepo();
foreach ($stale as $row) {
    $userRepo->anonymize((int) $row['id']);
}
$report['anonymized_users'] = count($stale);

Logger::info('cleanup.done', $report);
fwrite(STDOUT, "cleanup: " . json_encode($report) . "\n");

$release();
