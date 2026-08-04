<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Db;
use App\Models\User;
use App\Models\UserCollection;
use App\Models\Word;

final class ExclusiveWordService
{
    /** Below this many unclaimed words in a band's pool, alert admins (~14 days of runway). */
    private const LOW_POOL_THRESHOLD = 14;

    public function unlock(string $userId): ?array
    {
        $user = User::find($userId);
        $band = $user && $user['target_band'] ? (int) ceil((float) $user['target_band']) : 7;
        $band = max(6, min(9, $band));

        $word = Word::randomExclusiveForBand($band, $userId);
        $usedBand = $band;

        // Fallback to the next-lower band pool if this band is exhausted (§8 edge case).
        if ($word === null) {
            for ($fallbackBand = $band - 1; $fallbackBand >= 6; $fallbackBand--) {
                $word = Word::randomExclusiveForBand($fallbackBand, $userId);
                if ($word !== null) {
                    $usedBand = $fallbackBand;
                    break;
                }
            }
        }

        if ($word === null) {
            $this->alertPoolEmpty($band);
            return null;
        }

        UserCollection::add($userId, $word['id']);
        $this->checkRunway($usedBand);

        return $word;
    }

    private function checkRunway(int $band): void
    {
        $remaining = Word::countUnclaimedExclusive($band);
        if ($remaining < self::LOW_POOL_THRESHOLD) {
            $this->raiseAlert('exclusive_pool_low', ['band' => $band, 'remaining' => $remaining]);
        }
    }

    private function alertPoolEmpty(int $band): void
    {
        $this->raiseAlert('exclusive_pool_empty', ['band' => $band]);
    }

    private function raiseAlert(string $type, array $context): void
    {
        // Avoid spamming: skip if an unresolved alert of this type+band already exists.
        $stmt = Db::pdo()->prepare(
            "SELECT id FROM admin_alerts WHERE alert_type = :type AND resolved = FALSE
             AND JSON_EXTRACT(context, '$.band') = :band LIMIT 1"
        );
        $stmt->execute(['type' => $type, 'band' => $context['band']]);
        if ($stmt->fetch()) {
            return;
        }

        $ins = Db::pdo()->prepare(
            'INSERT INTO admin_alerts (id, alert_type, context, resolved, created_at)
             VALUES (:id, :type, :context, FALSE, NOW())'
        );
        $ins->execute([
            'id' => Db::uuid(),
            'type' => $type,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
        ]);

        error_log("[ExclusiveWordService] ALERT {$type}: " . json_encode($context));
    }
}
