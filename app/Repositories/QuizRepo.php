<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class QuizRepo
{
    public function find(int $id): ?array
    {
        return Db::first('SELECT * FROM quizzes WHERE id = ?', [$id]);
    }

    /** Random quiz set drawn from words the user has already started learning. */
    public function randomSetForUser(int $userId, int $count = 10): array
    {
        $rows = Db::all(
            'SELECT q.* FROM quizzes q
             JOIN user_word_progress p ON p.word_id = q.word_id AND p.user_id = ?
             ORDER BY RAND() LIMIT ' . (int) $count,
            [$userId]
        );

        if (count($rows) < $count) {
            // Not enough personalised quizzes yet — top up so the session isn't empty.
            $fallback = Db::all('SELECT * FROM quizzes ORDER BY RAND() LIMIT ' . (int) ($count - count($rows)));
            $rows = array_merge($rows, $fallback);
        }

        return $rows;
    }
}
