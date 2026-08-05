<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class DailyProgressRepo
{
    public function today(int $userId, int $goalTarget): array
    {
        $row = Db::first('SELECT * FROM daily_progress WHERE user_id = ? AND progress_date = CURDATE()', [$userId]);
        if ($row !== null) {
            return $row;
        }

        Db::insert(
            'INSERT INTO daily_progress (user_id, progress_date, goal_target, goal_achieved, goal_completed)
             VALUES (?, CURDATE(), ?, 0, 0)',
            [$userId, $goalTarget]
        );

        return (array) Db::first('SELECT * FROM daily_progress WHERE user_id = ? AND progress_date = CURDATE()', [$userId]);
    }

    public function increment(int $id): array
    {
        Db::exec('UPDATE daily_progress SET goal_achieved = goal_achieved + 1 WHERE id = ?', [$id]);
        return (array) Db::first('SELECT * FROM daily_progress WHERE id = ?', [$id]);
    }

    public function markCompleted(int $id, int $exclusiveWordId): void
    {
        Db::exec('UPDATE daily_progress SET goal_completed = 1, exclusive_word_id = ? WHERE id = ?', [$exclusiveWordId, $id]);
    }

    public function countCompletedInLastDays(int $userId, int $days = 7): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM daily_progress
              WHERE user_id = ? AND goal_completed = 1 AND progress_date >= (CURDATE() - INTERVAL ? DAY)',
            [$userId, $days]
        );
    }

    /** Completed-goal dates within a calendar month, for CalendarController. */
    public function completedDatesInMonth(int $userId, int $year, int $month): array
    {
        $rows = Db::all(
            'SELECT progress_date FROM daily_progress
              WHERE user_id = ? AND goal_completed = 1
                AND YEAR(progress_date) = ? AND MONTH(progress_date) = ?',
            [$userId, $year, $month]
        );
        return array_map(static fn($r) => (string) $r['progress_date'], $rows);
    }
}
