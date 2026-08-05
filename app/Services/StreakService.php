<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\StreakRepo;

final class StreakService
{
    public function __construct(private StreakRepo $repo = new StreakRepo())
    {
    }

    public function onGoalCompleted(int $userId): array
    {
        $this->repo->refillFreezeIfDue($userId);
        $streak = $this->repo->forUser($userId);

        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $last      = $streak['last_completed_date'];

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
            'current_streak'      => $current,
            'longest_streak'      => max($current, (int) $streak['longest_streak']),
            'freezes_available'   => $freezes,
            'last_completed_date' => $today,
        ];

        $this->repo->save($userId, $fields);

        return array_merge($streak, $fields);
    }
}
