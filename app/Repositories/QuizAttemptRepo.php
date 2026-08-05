<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class QuizAttemptRepo
{
    public function record(int $userId, int $quizId, bool $isCorrect): void
    {
        Db::exec(
            'INSERT INTO quiz_attempts (user_id, quiz_id, is_correct, attempted_at) VALUES (?, ?, ?, NOW())',
            [$userId, $quizId, $isCorrect ? 1 : 0]
        );
    }

    public function accuracyForUser(int $userId): float
    {
        $row = Db::first(
            'SELECT SUM(is_correct) AS correct, COUNT(*) AS total FROM quiz_attempts WHERE user_id = ?',
            [$userId]
        );
        $total = (int) ($row['total'] ?? 0);
        if ($total === 0) {
            return 0.0;
        }
        return round(((int) $row['correct'] / $total) * 100, 1);
    }
}
