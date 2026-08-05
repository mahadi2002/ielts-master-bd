<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Repositories\AdminAlertRepo;
use App\Repositories\CollectionRepo;
use App\Repositories\UserRepo;
use App\Repositories\WordRepo;

/**
 * Selects the reward word for a completed daily goal, weighted toward the
 * user's target band, and guards the reward pool from silently running dry.
 */
final class ExclusiveWordService
{
    /** Below this many unclaimed words in a band's pool, alert admins (~14 days of runway). */
    private const LOW_POOL_THRESHOLD = 14;

    public function __construct(
        private WordRepo $words = new WordRepo(),
        private UserRepo $users = new UserRepo(),
        private CollectionRepo $collection = new CollectionRepo(),
        private AdminAlertRepo $alerts = new AdminAlertRepo(),
    ) {
    }

    public function unlock(int $userId): ?array
    {
        $user = $this->users->find($userId);
        $band = $user && $user['target_band'] ? (int) ceil((float) $user['target_band']) : 7;
        $band = max(6, min(9, $band));

        $word     = $this->words->randomExclusiveForBand($band, $userId);
        $usedBand = $band;

        // Fallback to the next-lower band pool if this band is exhausted.
        if ($word === null) {
            for ($fallbackBand = $band - 1; $fallbackBand >= 6; $fallbackBand--) {
                $word = $this->words->randomExclusiveForBand($fallbackBand, $userId);
                if ($word !== null) {
                    $usedBand = $fallbackBand;
                    break;
                }
            }
        }

        if ($word === null) {
            $this->raise('exclusive_pool_empty', $band);
            return null;
        }

        $this->collection->add($userId, (int) $word['id']);
        $this->checkRunway($usedBand);

        return $word;
    }

    private function checkRunway(int $band): void
    {
        $remaining = $this->words->countUnclaimedExclusive($band);
        if ($remaining < self::LOW_POOL_THRESHOLD) {
            $this->raise('exclusive_pool_low', $band, $remaining);
        }
    }

    private function raise(string $type, int $band, ?int $remaining = null): void
    {
        // Avoid spamming: skip if an unresolved alert of this type+band already exists.
        if ($this->alerts->hasUnresolved($type, $band)) {
            return;
        }

        $context = $remaining === null ? ['band' => $band] : ['band' => $band, 'remaining' => $remaining];
        $this->alerts->raise($type, $context);

        Logger::warning('exclusive_word.' . $type, $context);
    }
}
