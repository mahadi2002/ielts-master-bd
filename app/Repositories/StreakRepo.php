<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class StreakRepo
{
    public function forUser(int $userId): array
    {
        $row = Db::first('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
        if ($row !== null) {
            return $row;
        }

        Db::exec(
            'INSERT INTO streaks (user_id, current_streak, longest_streak, freezes_available, freezes_reset_at, last_completed_date)
             VALUES (?, 0, 0, 1, CURDATE(), NULL)',
            [$userId]
        );

        return (array) Db::first('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
    }

    public function save(int $userId, array $fields): void
    {
        Db::exec(
            'UPDATE streaks SET current_streak = ?, longest_streak = ?, freezes_available = ?, last_completed_date = ?
              WHERE user_id = ?',
            [$fields['current_streak'], $fields['longest_streak'], $fields['freezes_available'], $fields['last_completed_date'], $userId]
        );
    }

    public function refillFreezeIfDue(int $userId): void
    {
        $streak = $this->forUser($userId);
        if ($streak['freezes_reset_at'] !== null && $streak['freezes_reset_at'] <= date('Y-m-d')) {
            Db::exec(
                'UPDATE streaks SET freezes_available = 1, freezes_reset_at = ? WHERE user_id = ?',
                [date('Y-m-d', strtotime('+7 days')), $userId]
            );
        }
    }

    /** Every streak that has gone quiet — feeds the daily cron's break check. */
    public function staleStreaks(string $yesterday): array
    {
        return Db::all(
            'SELECT * FROM streaks WHERE current_streak > 0 AND last_completed_date IS NOT NULL AND last_completed_date < ?',
            [$yesterday]
        );
    }

    public function breakStreak(int $userId): void
    {
        Db::exec('UPDATE streaks SET current_streak = 0 WHERE user_id = ?', [$userId]);
    }
}
