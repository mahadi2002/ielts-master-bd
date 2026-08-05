<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exceptions\HttpException;
use App\Repositories\UserRepo;

/**
 * Admin is a role on the same users table everyone else is in, not a
 * separate identity — staff sign in through the exact same phone-OTP
 * subscribe flow as any other user. This middleware only adds the role
 * check on top of the ordinary logged-in state.
 */
final class RequireAdmin implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $allowlist = (array) config('app.admin_ip_allowlist', []);
        if ($allowlist !== [] && !in_array($request->ip(), $allowlist, true)) {
            throw new HttpException(403);
        }

        $userId = Session::userId();
        if ($userId === null) {
            Session::notify('info', 'এই অংশ দেখতে আগে Login করুন।');
            return Response::redirect('/subscribe?next=' . rawurlencode($request->path));
        }

        $user = (new UserRepo())->find($userId);
        if ($user === null || $user['status'] === 'blocked' || $user['role'] !== 'admin') {
            throw new HttpException(403);
        }

        View::share('admin', $user);

        return $next();
    }
}
