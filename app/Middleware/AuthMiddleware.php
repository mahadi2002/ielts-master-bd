<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (Session::userId() === null) {
            // Anonymous sessions aren't persisted (sessions.user_id is NOT NULL) —
            // a flash message here wouldn't survive the redirect, so the
            // mobile-entry page's own copy carries the "please subscribe" framing.
            Response::redirect('/subscribe');
            return false;
        }
        return true;
    }
}
