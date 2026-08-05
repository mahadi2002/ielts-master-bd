<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CollectionRepo;
use App\Repositories\DailyProgressRepo;
use App\Repositories\ProgressRepo;
use App\Repositories\QuizAttemptRepo;
use App\Repositories\StreakRepo;
use App\Repositories\UserRepo;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $user   = (new UserRepo())->find($userId);
        $target = (int) ($user['daily_goal_count'] ?? 5);

        $progressRepo = new ProgressRepo();

        return $this->view('app/dashboard', [
            'title'             => 'Dashboard',
            'statusCounts'      => $progressRepo->countByStatus($userId),
            'weakWords'         => $progressRepo->weakWords($userId, 8),
            'weeklyActivity'    => $progressRepo->activityByDay($userId, 7),
            'streak'            => (new StreakRepo())->forUser($userId),
            'todayProgress'     => (new DailyProgressRepo())->today($userId, $target),
            'collectionCount'   => (new CollectionRepo())->count($userId),
            'accuracy'          => (new QuizAttemptRepo())->accuracyForUser($userId),
            'completedThisWeek' => (new DailyProgressRepo())->countCompletedInLastDays($userId, 7),
        ]);
    }
}
