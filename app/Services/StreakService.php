<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Streak;

final class StreakService
{
    public function onGoalCompleted(string $userId): array
    {
        Streak::refillFreezeIfDue($userId);
        $streak = Streak::forUser($userId);

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $last = $streak['last_completed_date'];

        if ($last === $today) {
            return $streak; // already recorded today
        }

        if ($last === null) {
            $current = 1;
            $freezes = (int) $streak['freezes_available'];
        } elseif ($last === $yesterday) {
            $current = (int) $streak['current_streak'] + 1;
            $freezes = (int) $streak['freezes_available'];
        } else {
            $daysMissed = (int) round((strtotime($today) - strtotime($last)) / 86400) - 1;
            if ($daysMissed === 1 && (int) $streak['freezes_available'] > 0) {
                $current = (int) $streak['current_streak'] + 1;
                $freezes = (int) $streak['freezes_available'] - 1;
            } else {
                $current = 1;
                $freezes = (int) $streak['freezes_available'];
            }
        }

        $fields = [
            'current_streak' => $current,
            'longest_streak' => max($current, (int) $streak['longest_streak']),
            'freezes_available' => $freezes,
            'last_completed_date' => $today,
        ];

        Streak::save($userId, $fields);

        return array_merge($streak, $fields);
    }
}
