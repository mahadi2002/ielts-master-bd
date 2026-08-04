<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class DailyProgress
{
    public static function today(string $userId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT * FROM daily_progress WHERE user_id = :u AND progress_date = CURDATE()'
        );
        $stmt->execute(['u' => $userId]);
        $row = $stmt->fetch();

        if ($row) {
            return $row;
        }

        $user = User::find($userId);
        $id = Db::uuid();
        $ins = Db::pdo()->prepare(
            'INSERT INTO daily_progress (id, user_id, progress_date, goal_target, goal_achieved, goal_completed)
             VALUES (:id, :u, CURDATE(), :target, 0, FALSE)'
        );
        $ins->execute(['id' => $id, 'u' => $userId, 'target' => $user['daily_goal_count'] ?? 5]);

        $stmt->execute(['u' => $userId]);
        return $stmt->fetch();
    }

    public static function increment(string $id): array
    {
        Db::pdo()->prepare('UPDATE daily_progress SET goal_achieved = goal_achieved + 1 WHERE id = :id')
            ->execute(['id' => $id]);
        $stmt = Db::pdo()->prepare('SELECT * FROM daily_progress WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function markCompleted(string $id, string $exclusiveWordId): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE daily_progress SET goal_completed = TRUE, exclusive_word_id = :word WHERE id = :id'
        );
        $stmt->execute(['word' => $exclusiveWordId, 'id' => $id]);
    }

    public static function countCompletedInLastDays(string $userId, int $days = 7): int
    {
        $stmt = Db::pdo()->prepare(
            'SELECT COUNT(*) c FROM daily_progress
             WHERE user_id = :u AND goal_completed = TRUE AND progress_date >= (CURDATE() - INTERVAL :days DAY)'
        );
        $stmt->bindValue('u', $userId);
        $stmt->bindValue('days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch()['c'];
    }
}
