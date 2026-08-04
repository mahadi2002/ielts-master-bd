<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Quiz
{
    public static function find(string $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM quizzes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Random quiz set drawn from words the user has already started learning. */
    public static function randomSetForUser(string $userId, int $count = 10): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT q.* FROM quizzes q
             JOIN user_word_progress p ON p.word_id = q.word_id AND p.user_id = :u
             ORDER BY RAND() LIMIT :limit"
        );
        $stmt->bindValue('u', $userId);
        $stmt->bindValue('limit', $count, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (count($rows) < $count) {
            // Not enough personalised quizzes yet — top up with any quiz so the session isn't empty.
            $fallback = Db::pdo()->prepare('SELECT * FROM quizzes ORDER BY RAND() LIMIT :limit');
            $fallback->bindValue('limit', $count - count($rows), \PDO::PARAM_INT);
            $fallback->execute();
            $rows = array_merge($rows, $fallback->fetchAll());
        }

        return $rows;
    }
}
