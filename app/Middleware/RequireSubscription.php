<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\StreakRepo;
use App\Services\SubscriptionService;

/**
 * The one rule this whole gate exists to enforce: every gated request
 * re-reads the database. No session flag, no cookie, no token claim ever
 * decides access on its own.
 */
final class RequireSubscription implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $userId = Session::userId();

        if ($userId === null || !SubscriptionService::hasAccess($userId)) {
            return Response::redirect('/expired');
        }

        // Every gated view's header shows the streak flame — share it once here
        // rather than every controller re-fetching it.
        $streak = (new StreakRepo())->forUser($userId);
        View::share('streakCount', (int) $streak['current_streak']);

        return $next();
    }
}
