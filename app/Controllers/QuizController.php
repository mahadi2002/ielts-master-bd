<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\ExclusiveWordService;
use App\Services\GoalService;
use App\Services\StreakService;

final class QuizController
{
    public function index(Request $request): void
    {
        $userId = (string) Session::userId();
        $quizzes = Quiz::randomSetForUser($userId, 10);

        foreach ($quizzes as &$quiz) {
            if ($quiz['options']) {
                $quiz['options'] = json_decode($quiz['options'], true);
            }
        }
        unset($quiz);

        Response::html(View::render('quiz/index', ['quizzes' => $quizzes]));
    }

    public function submit(Request $request): void
    {
        $userId = (string) Session::userId();
        $quizId = (string) $request->input('quiz_id');
        $answer = (string) $request->input('answer');

        $quiz = Quiz::find($quizId);
        if (!$quiz) {
            Response::json(['error' => 'পাওয়া যায়নি।'], 404);
            return;
        }

        $isCorrect = mb_strtolower(trim($answer)) === mb_strtolower(trim($quiz['correct_answer']));
        QuizAttempt::record($userId, $quizId, $isCorrect);

        $justCompleted = false;
        $unlockedWord = null;

        if ($isCorrect) {
            $goalService = new GoalService(new ExclusiveWordService(), new StreakService());
            $result = $goalService->increment($userId);
            $justCompleted = $result['justCompleted'];
            $unlockedWord = $result['unlockedWord'];
        }

        Response::json([
            'success' => true,
            'correct' => $isCorrect,
            'correctAnswer' => $quiz['correct_answer'],
            'justCompleted' => $justCompleted,
            'unlockedWord' => $unlockedWord,
        ]);
    }
}
