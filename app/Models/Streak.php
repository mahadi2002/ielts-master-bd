<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Streak
{
    public static function forUser(string $userId): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM streaks WHERE user_id = :u');
        $stmt->execute(['u' => $userId]);
        $row = $stmt->fetch();

        if ($row) {
            return $row;
        }

        $ins = Db::pdo()->prepare(
            'INSERT INTO streaks (user_id, current_streak, longest_streak, freezes_available, freezes_reset_at, last_completed_date)
             VALUES (:u, 0, 0, 1, CURDATE(), NULL)'
        );
        $ins->execute(['u' => $userId]);

        $stmt->execute(['u' => $userId]);
        return $stmt->fetch();
    }

    public static function save(string $userId, array $fields): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE streaks SET current_streak = :cur, longest_streak = :longest,
                freezes_available = :freezes, last_completed_date = :last
             WHERE user_id = :u'
        );
        $stmt->execute([
            'cur' => $fields['current_streak'],
            'longest' => $fields['longest_streak'],
            'freezes' => $fields['freezes_available'],
            'last' => $fields['last_completed_date'],
            'u' => $userId,
        ]);
    }

    public static function refillFreezeIfDue(string $userId): void
    {
        $streak = self::forUser($userId);
        if ($streak['freezes_reset_at'] !== null && $streak['freezes_reset_at'] <= date('Y-m-d')) {
            $stmt = Db::pdo()->prepare(
                'UPDATE streaks SET freezes_available = 1, freezes_reset_at = :next WHERE user_id = :u'
            );
            $stmt->execute(['next' => date('Y-m-d', strtotime('+7 days')), 'u' => $userId]);
        }
    }
}
