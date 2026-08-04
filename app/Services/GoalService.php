<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailyProgress;

final class GoalService
{
    public function __construct(
        private ExclusiveWordService $exclusiveWordService,
        private StreakService $streakService,
    ) {
    }

    /**
     * Registers one unit of daily-goal progress (a learned word or a correct quiz answer).
     * @return array{progress: array, justCompleted: bool, unlockedWord: ?array}
     */
    public function increment(string $userId): array
    {
        $progress = DailyProgress::today($userId);

        if ($progress['goal_completed']) {
            return ['progress' => $progress, 'justCompleted' => false, 'unlockedWord' => null];
        }

        $progress = DailyProgress::increment($progress['id']);

        $justCompleted = false;
        $unlockedWord = null;

        if (!$progress['goal_completed'] && (int) $progress['goal_achieved'] >= (int) $progress['goal_target']) {
            $unlockedWord = $this->exclusiveWordService->unlock($userId);
            if ($unlockedWord) {
                DailyProgress::markCompleted($progress['id'], $unlockedWord['id']);
            }
            $this->streakService->onGoalCompleted($userId);
            $justCompleted = true;

            $progress = DailyProgress::today($userId);
        }

        return ['progress' => $progress, 'justCompleted' => $justCompleted, 'unlockedWord' => $unlockedWord];
    }
}
