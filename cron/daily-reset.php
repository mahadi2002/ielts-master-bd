<?php

declare(strict_types=1);

/**
 * Midnight reset + streak-break check.
 *
 * daily_progress rows are date-scoped and created lazily by DailyProgress::today(),
 * so there is nothing to delete here. This script's job is the streak side:
 * break streaks for users who went silent past their single-day freeze grace,
 * without touching users StreakService can still resolve on their next visit.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;

function breakStaleStreaks(\PDO $pdo): int
{
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $stmt = $pdo->prepare(
        "SELECT * FROM streaks WHERE current_streak > 0 AND last_completed_date IS NOT NULL AND last_completed_date < :yesterday"
    );
    $stmt->execute(['yesterday' => $yesterday]);
    $rows = $stmt->fetchAll();

    $broken = 0;
    foreach ($rows as $row) {
        $daysSinceLast = (int) round((strtotime($today) - strtotime($row['last_completed_date'])) / 86400);
        $missedDays = $daysSinceLast - 1;

        $withinGrace = $missedDays === 1 && (int) $row['freezes_available'] > 0;
        if ($withinGrace) {
            continue; // StreakService resolves this (consumes the freeze) on the user's next completion.
        }

        $upd = $pdo->prepare('UPDATE streaks SET current_streak = 0 WHERE user_id = :u');
        $upd->execute(['u' => $row['user_id']]);
        $broken++;
    }

    return $broken;
}

$config = require dirname(__DIR__) . '/config.php';
Db::connect($config);

$broken = breakStaleStreaks(Db::pdo());
echo "[daily-reset] " . date('Y-m-d H:i:s') . " — streaks broken: {$broken}\n";
