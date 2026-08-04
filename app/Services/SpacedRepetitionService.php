<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserWordProgress;

/**
 * SM-2 spaced repetition scheduling. Ratings map to SM-2 "quality" (0-5):
 * again=0, hard=2, good=4, easy=5. Quality < 3 resets the card.
 */
final class SpacedRepetitionService
{
    private const RATING_QUALITY = [
        'again' => 0,
        'hard' => 2,
        'good' => 4,
        'easy' => 5,
    ];

    public function submitAnswer(array $progress, string $rating): array
    {
        $quality = self::RATING_QUALITY[$rating] ?? 4;

        $easeFactor = (float) $progress['ease_factor'];
        $interval = (int) $progress['interval_days'];
        $repetitions = (int) $progress['repetitions'];

        if ($quality < 3) {
            $repetitions = 0;
            $interval = 1;
            $easeFactor = max(1.3, $easeFactor - 0.2);
        } else {
            $repetitions++;
            $interval = match (true) {
                $repetitions === 1 => 1,
                $repetitions === 2 => 6,
                default => (int) round($interval * $easeFactor),
            };
            $easeFactor = max(1.3, $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02)));
        }

        $status = match (true) {
            $interval >= 21 => 'mastered',
            $repetitions > 0 => 'review',
            default => 'learning',
        };

        $fields = [
            'status' => $status,
            'ease_factor' => round($easeFactor, 2),
            'interval_days' => max(1, $interval),
            'repetitions' => $repetitions,
            'next_review_date' => date('Y-m-d', strtotime("+{$interval} day")),
        ];

        UserWordProgress::updateSrs($progress['id'], $fields);

        return $fields;
    }
}
