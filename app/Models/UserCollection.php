<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class UserCollection
{
    public static function add(string $userId, string $wordId): void
    {
        $stmt = Db::pdo()->prepare(
            'INSERT IGNORE INTO user_collection (user_id, word_id, unlocked_at) VALUES (:u, :w, NOW())'
        );
        $stmt->execute(['u' => $userId, 'w' => $wordId]);
    }

    public static function forUser(string $userId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT uc.unlocked_at, w.* FROM user_collection uc
             JOIN words w ON w.id = uc.word_id
             WHERE uc.user_id = :u ORDER BY uc.unlocked_at DESC'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll();
    }

    public static function count(string $userId): int
    {
        $stmt = Db::pdo()->prepare('SELECT COUNT(*) c FROM user_collection WHERE user_id = :u');
        $stmt->execute(['u' => $userId]);
        return (int) $stmt->fetch()['c'];
    }
}
