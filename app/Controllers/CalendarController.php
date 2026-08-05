<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CalendarService;

final class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $year  = $request->int('y', (int) date('Y'));
        $month = $request->int('m', (int) date('n'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $grid = (new CalendarService())->monthGrid((int) $this->currentUserId(), $year, $month);

        $prev = $month === 1 ? ['y' => $year - 1, 'm' => 12] : ['y' => $year, 'm' => $month - 1];
        $next = $month === 12 ? ['y' => $year + 1, 'm' => 1] : ['y' => $year, 'm' => $month + 1];

        return $this->view('app/calendar', [
            'title' => 'Calendar',
            'grid'  => $grid,
            'prev'  => $prev,
            'next'  => $next,
        ]);
    }
}
