<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class CsrfMiddleware
{
    public function handle(Request $request): bool
    {
        $token = $request->input('_csrf');

        if (!Csrf::verify(is_string($token) ? $token : null)) {
            if ($request->wantsJson()) {
                Response::json(['error' => 'সেশন মেয়াদ শেষ, আবার চেষ্টা করুন।'], 419);
            } else {
                Response::html(View::render('errors/419', [], 'layout'), 419);
            }
            return false;
        }

        return true;
    }
}
