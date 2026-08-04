<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Core\Request;
use App\Core\Response;

final class HomeController
{
    public function landing(Request $request): void
    {
        $stmt = Db::pdo()->query(
            "SELECT * FROM words WHERE is_exclusive = FALSE ORDER BY RAND() LIMIT 1"
        );
        $wordOfTheDay = $stmt->fetch() ?: null;

        $quizStmt = Db::pdo()->query(
            "SELECT q.*, w.headword FROM quizzes q JOIN words w ON w.id = q.word_id
             WHERE q.quiz_type = 'mcq' ORDER BY RAND() LIMIT 1"
        );
        $sampleQuiz = $quizStmt->fetch() ?: null;
        if ($sampleQuiz && $sampleQuiz['options']) {
            $sampleQuiz['options'] = json_decode($sampleQuiz['options'], true);
        }

        Response::html(\App\Core\View::render('home/landing', [
            'wordOfTheDay' => $wordOfTheDay,
            'sampleQuiz' => $sampleQuiz,
        ]));
    }
}
