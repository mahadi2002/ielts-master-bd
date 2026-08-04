<?php

declare(strict_types=1);

/**
 * Single cron entry point (crontab runs this every 1-5 min). Drives the
 * `jobs` table plus the two time-based checks — one line covers every
 * scheduled task the app adds later.
 *
 * crontab: * * * * * php /path/to/ieltsmasterbd/cron/run-jobs.php >> storage/logs/cron.log 2>&1
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;

$pdo = Db::pdo();

// ---- Daily reset: only actually run once per calendar day ----
$today = date('Y-m-d');
$marker = $pdo->prepare("SELECT id FROM jobs WHERE job_type = 'daily_reset' AND DATE(run_at) = :today AND status = 'done'");
$marker->execute(['today' => $today]);
if (!$marker->fetch()) {
    $jobId = Db::uuid();
    $pdo->prepare("INSERT INTO jobs (id, job_type, status, run_at, created_at) VALUES (:id, 'daily_reset', 'running', NOW(), NOW())")
        ->execute(['id' => $jobId]);
    try {
        require_once __DIR__ . '/daily-reset.php';
        $pdo->prepare("UPDATE jobs SET status = 'done' WHERE id = :id")->execute(['id' => $jobId]);
    } catch (\Throwable $e) {
        $pdo->prepare("UPDATE jobs SET status = 'failed', attempts = attempts + 1 WHERE id = :id")->execute(['id' => $jobId]);
        error_log('[run-jobs] daily_reset failed: ' . $e->getMessage());
    }
}

// ---- Subscription charge-status check: cheap, safe to run every tick ----
require_once __DIR__ . '/subscription-check.php';

// ---- Drain any ad-hoc pending jobs queued by the app ----
$pending = $pdo->query("SELECT * FROM jobs WHERE status = 'pending' AND run_at <= NOW() AND job_type != 'daily_reset' LIMIT 20")->fetchAll();
foreach ($pending as $job) {
    $pdo->prepare("UPDATE jobs SET status = 'running' WHERE id = :id")->execute(['id' => $job['id']]);
    try {
        // Extend here as new job_types are introduced.
        error_log('[run-jobs] no handler registered for job_type: ' . $job['job_type']);
        $pdo->prepare("UPDATE jobs SET status = 'failed', attempts = attempts + 1 WHERE id = :id")->execute(['id' => $job['id']]);
    } catch (\Throwable $e) {
        $pdo->prepare("UPDATE jobs SET status = 'failed', attempts = attempts + 1 WHERE id = :id")->execute(['id' => $job['id']]);
        error_log('[run-jobs] job ' . $job['id'] . ' failed: ' . $e->getMessage());
    }
}
