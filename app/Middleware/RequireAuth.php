<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepo;

final class RequireAuth implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $userId = Session::userId();

        if ($userId === null) {
            Session::notify('info', 'এই অংশ দেখতে Subscribe করুন।');
            return Response::redirect('/subscribe?next=' . rawurlencode($request->path));
        }

        // The account may have been blocked since the session was created.
        $user = (new UserRepo())->find($userId);
        if ($user === null || $user['status'] === 'blocked') {
            Session::revokeAllForUser($userId);
            Session::destroy_all();
            return Response::redirect('/subscribe');
        }

        return $next();
    }
}
