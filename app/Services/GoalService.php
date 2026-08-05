<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\DailyProgressRepo;
use App\Repositories\UserRepo;

final class GoalService
{
    public function __construct(
        private ExclusiveWordService $exclusiveWordService = new ExclusiveWordService(),
        private StreakService $streakService = new StreakService(),
        private DailyProgressRepo $progressRepo = new DailyProgressRepo(),
        private UserRepo $userRepo = new UserRepo(),
    ) {
    }

    /**
     * Registers one unit of daily-goal progress (a learned word or a correct
     * quiz answer).
     * @return array{progress: array, justCompleted: bool, unlockedWord: ?array}
     */
    public function increment(int $userId): array
    {
        $user   = $this->userRepo->find($userId);
        $target = (int) ($user['daily_goal_count'] ?? 5);

        $progress = $this->progressRepo->today($userId, $target);

        if ((bool) $progress['goal_completed']) {
            return ['progress' => $progress, 'justCompleted' => false, 'unlockedWord' => null];
        }

        $progress = $this->progressRepo->increment((int) $progress['id']);

        $justCompleted = false;
        $unlockedWord  = null;

        if (!(bool) $progress['goal_completed'] && (int) $progress['goal_achieved'] >= (int) $progress['goal_target']) {
            $unlockedWord = $this->exclusiveWordService->unlock($userId);
            if ($unlockedWord !== null) {
                $this->progressRepo->markCompleted((int) $progress['id'], (int) $unlockedWord['id']);
            }
            $this->streakService->onGoalCompleted($userId);
            $justCompleted = true;

            $progress = $this->progressRepo->today($userId, $target);
        }

        return ['progress' => $progress, 'justCompleted' => $justCompleted, 'unlockedWord' => $unlockedWord];
    }
}
