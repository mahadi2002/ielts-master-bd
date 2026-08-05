<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/** user_collection — exclusive words unlocked by completing daily goals. */
final class CollectionRepo
{
    public function add(int $userId, int $wordId): void
    {
        Db::exec('INSERT IGNORE INTO user_collection (user_id, word_id, unlocked_at) VALUES (?, ?, NOW())', [$userId, $wordId]);
    }

    public function forUser(int $userId): array
    {
        return Db::all(
            'SELECT uc.unlocked_at, w.* FROM user_collection uc
             JOIN words w ON w.id = uc.word_id
             WHERE uc.user_id = ? ORDER BY uc.unlocked_at DESC',
            [$userId]
        );
    }

    public function count(int $userId): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM user_collection WHERE user_id = ?', [$userId]);
    }

    public function has(int $userId, int $wordId): bool
    {
        return Db::value('SELECT 1 FROM user_collection WHERE user_id = ? AND word_id = ?', [$userId, $wordId]) !== null;
    }
}
