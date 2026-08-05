<?php
declare(strict_types=1);

/**
 * Daily 00:15 — streak upkeep.
 *
 * daily_progress rows are date-scoped and created lazily by DailyProgressRepo,
 * so there is nothing to reset there. This job's job is the streak side:
 * break streaks for users who went silent past their single-day freeze
 * grace, without touching users StreakService can still resolve normally
 * the next time they actually complete a goal.
 *
 * Cron entry:
 *   15 0 * * *  /usr/local/bin/php /home/USER/ieltsmasterbd/cron/study_tasks.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Logger;
use App\Repositories\StreakRepo;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('study_tasks');
if ($release === null) {
    exit(0);
}

$repo      = new StreakRepo();
$yesterday = date('Y-m-d', strtotime('-1 day'));
$today     = date('Y-m-d');

$stale  = $repo->staleStreaks($yesterday);
$broken = 0;

foreach ($stale as $streak) {
    $daysSinceLast = (int) round((strtotime($today) - strtotime((string) $streak['last_completed_date'])) / 86400);
    $missedDays    = $daysSinceLast - 1;

    $withinGrace = $missedDays === 1 && (int) $streak['freezes_available'] > 0;
    if ($withinGrace) {
        // StreakService consumes the freeze itself on the user's next completion.
        continue;
    }

    $repo->breakStreak((int) $streak['user_id']);
    $broken++;
}

Logger::info('study_tasks.done', ['streaks_broken' => $broken, 'checked' => count($stale)]);
fwrite(STDOUT, "study_tasks: broke $broken stale streak(s) out of " . count($stale) . " checked.\n");

$release();
