<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\DailyProgress;
use App\Models\User;
use App\Models\UserWordProgress;
use App\Models\Word;
use App\Services\ExclusiveWordService;
use App\Services\GoalService;
use App\Services\StreakService;

final class LearnController
{
    public function session(Request $request): void
    {
        $userId = (string) Session::userId();
        $progress = DailyProgress::today($userId);
        $user = User::find($userId);

        if ($progress['goal_completed']) {
            Response::html(View::render('learn/session', [
                'progress' => $progress,
                'words' => [],
                'completed' => true,
            ]));
            return;
        }

        $remaining = max(1, (int) $progress['goal_target'] - (int) $progress['goal_achieved']);
        $words = Word::nextForLearning($userId, $user['target_band'] ?? null, $remaining);

        foreach ($words as &$word) {
            if ($word['synonyms']) {
                $word['synonyms'] = json_decode($word['synonyms'], true);
            }
        }
        unset($word);

        Response::html(View::render('learn/session', [
            'progress' => $progress,
            'words' => $words,
            'completed' => false,
        ]));
    }

    public function markWord(Request $request): void
    {
        $userId = (string) Session::userId();
        $wordId = (string) $request->input('word_id');

        $word = Word::find($wordId);
        if ($word === null) {
            Response::json(['error' => 'শব্দ পাওয়া যায়নি।'], 404);
            return;
        }

        UserWordProgress::upsertLearned($userId, $wordId);

        $goalService = new GoalService(new ExclusiveWordService(), new StreakService());
        $result = $goalService->increment($userId);

        Response::json([
            'success' => true,
            'progress' => [
                'achieved' => (int) $result['progress']['goal_achieved'],
                'target' => (int) $result['progress']['goal_target'],
                'completed' => (bool) $result['progress']['goal_completed'],
            ],
            'justCompleted' => $result['justCompleted'],
            'unlockedWord' => $result['unlockedWord'],
        ]);
    }
}
