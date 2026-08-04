<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class User
{
    public static function findByMobile(string $mobile): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM users WHERE mobile_number = :m');
        $stmt->execute(['m' => $mobile]);
        return $stmt->fetch() ?: null;
    }

    public static function find(string $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $mobile, string $operator): array
    {
        $id = Db::uuid();
        $stmt = Db::pdo()->prepare(
            'INSERT INTO users (id, mobile_number, operator, created_at) VALUES (:id, :mobile, :operator, NOW())'
        );
        $stmt->execute(['id' => $id, 'mobile' => $mobile, 'operator' => $operator]);
        return self::find($id);
    }

    public static function touchActivity(string $id): void
    {
        $stmt = Db::pdo()->prepare('UPDATE users SET last_active_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function setGoal(string $id, string $type, int $count): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE users SET daily_goal_type = :type, daily_goal_count = :count WHERE id = :id'
        );
        $stmt->execute(['type' => $type, 'count' => $count, 'id' => $id]);
    }

    public static function isAdmin(array $user): bool
    {
        return ($user['role'] ?? 'user') === 'admin';
    }

    public static function countAll(): int
    {
        return (int) Db::pdo()->query('SELECT COUNT(*) c FROM users')->fetch()['c'];
    }

    public static function paginate(int $limit = 50, int $offset = 0): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
