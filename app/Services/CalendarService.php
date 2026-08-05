<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\DailyProgressRepo;

/**
 * The monthly study calendar — a completed goal on a day lights that day up.
 * The time-based-guidance counterpart to the catalog (words) and the
 * diagnostic engine (spaced repetition): a reason to open the app every day,
 * not just when something needs reviewing.
 */
final class CalendarService
{
    public function __construct(private DailyProgressRepo $repo = new DailyProgressRepo())
    {
    }

    /**
     * @return array{year:int, month:int, weeks:array<int,array<int,array{date:?string,completed:bool,isToday:bool}>>}
     */
    public function monthGrid(int $userId, int $year, int $month): array
    {
        $completed = array_flip($this->repo->completedDatesInMonth($userId, $year, $month));

        $firstOfMonth = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth  = (int) date('t', $firstOfMonth);
        // Monday-first grid: 0 = Monday .. 6 = Sunday.
        $startOffset = ((int) date('N', $firstOfMonth)) - 1;
        $today       = date('Y-m-d');

        $cells = array_fill(0, $startOffset, null);
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date    = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $cells[] = [
                'date'      => $date,
                'day'       => $day,
                'completed' => isset($completed[$date]),
                'isToday'   => $date === $today,
            ];
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return [
            'year'  => $year,
            'month' => $month,
            'weeks' => array_chunk($cells, 7),
        ];
    }
}
