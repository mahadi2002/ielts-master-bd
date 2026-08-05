<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\QuizAttemptRepo;
use App\Repositories\QuizRepo;
use App\Services\GoalService;

final class QuizController extends Controller
{
    public function __construct(
        private QuizRepo $quizzes = new QuizRepo(),
        private QuizAttemptRepo $attempts = new QuizAttemptRepo(),
    ) {
    }

    public function index(Request $request): Response
    {
        $userId  = (int) $this->currentUserId();
        $quizSet = $this->quizzes->randomSetForUser($userId, 10);

        foreach ($quizSet as &$quiz) {
            if ($quiz['options']) {
                $quiz['options'] = json_decode((string) $quiz['options'], true);
            }
        }
        unset($quiz);

        return $this->view('app/quiz', ['title' => 'Quiz', 'quizzes' => $quizSet]);
    }

    public function submit(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $quizId = $request->int('quiz_id');
        $answer = $request->str('answer');

        $quiz = $this->quizzes->find($quizId);
        if ($quiz === null) {
            return $this->json(['error' => 'পাওয়া যায়নি।'], 404);
        }

        $isCorrect = mb_strtolower($answer) === mb_strtolower((string) $quiz['correct_answer']);
        $this->attempts->record($userId, $quizId, $isCorrect);

        $justCompleted = false;
        $unlockedWord  = null;

        if ($isCorrect) {
            $result        = (new GoalService())->increment($userId);
            $justCompleted = $result['justCompleted'];
            $unlockedWord  = $result['unlockedWord'];
        }

        return $this->json([
            'success'       => true,
            'correct'       => $isCorrect,
            'correctAnswer' => $quiz['correct_answer'],
            'justCompleted' => $justCompleted,
            'unlockedWord'  => $unlockedWord,
        ]);
    }
}
