<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class QuizAttempt
{
    public static function record(string $userId, string $quizId, bool $isCorrect): void
    {
        $stmt = Db::pdo()->prepare(
            'INSERT INTO quiz_attempts (id, user_id, quiz_id, is_correct, attempted_at)
             VALUES (:id, :u, :q, :correct, NOW())'
        );
        $stmt->execute([
            'id' => Db::uuid(),
            'u' => $userId,
            'q' => $quizId,
            'correct' => $isCorrect ? 1 : 0,
        ]);
    }

    public static function accuracyForUser(string $userId): float
    {
        $stmt = Db::pdo()->prepare(
            'SELECT SUM(is_correct) correct, COUNT(*) total FROM quiz_attempts WHERE user_id = :u'
        );
        $stmt->execute(['u' => $userId]);
        $row = $stmt->fetch();
        $total = (int) ($row['total'] ?? 0);
        if ($total === 0) {
            return 0.0;
        }
        return round(((int) $row['correct'] / $total) * 100, 1);
    }
}
