<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\DailyProgress;
use App\Models\QuizAttempt;
use App\Models\Streak;
use App\Models\UserCollection;
use App\Models\UserWordProgress;

final class DashboardController
{
    public function index(Request $request): void
    {
        $userId = (string) Session::userId();

        $statusCounts = UserWordProgress::countByStatus($userId);
        $weakWords = UserWordProgress::weakWords($userId, 8);
        $weeklyActivity = UserWordProgress::learnedInLastDays($userId, 7);
        $streak = Streak::forUser($userId);
        $todayProgress = DailyProgress::today($userId);
        $collectionCount = UserCollection::count($userId);
        $accuracy = QuizAttempt::accuracyForUser($userId);
        $completedThisWeek = DailyProgress::countCompletedInLastDays($userId, 7);

        Response::html(View::render('dashboard/index', [
            'statusCounts' => $statusCounts,
            'weakWords' => $weakWords,
            'weeklyActivity' => $weeklyActivity,
            'streak' => $streak,
            'todayProgress' => $todayProgress,
            'collectionCount' => $collectionCount,
            'accuracy' => $accuracy,
            'completedThisWeek' => $completedThisWeek,
        ]));
    }
}
