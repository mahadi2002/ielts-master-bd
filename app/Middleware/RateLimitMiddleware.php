<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class RateLimitMiddleware
{
    public function handle(Request $request): bool
    {
        $cfg = require BASE_PATH . '/config.php';

        if (str_starts_with($request->path, '/dictionary')) {
            $bucket = 'dict:ip:' . $request->ip();
            $ok = RateLimiter::attempt($bucket, $cfg['rate_limits']['dict_free_per_day'], 86400);
            if (!$ok) {
                // Inline banner on the dictionary page itself (§8), not a redirect —
                // anonymous sessions aren't persisted, so a flash would be lost.
                Response::html(View::render('dictionary/search', [
                    'query' => (string) $request->query('q', ''),
                    'results' => [],
                    'rateLimited' => true,
                ]), 429);
                return false;
            }
            return true;
        }

        if (str_starts_with($request->path, '/subscribe/otp')) {
            $mobile = (string) $request->input('mobile_number', $request->ip());
            $bucket = 'otp:' . $mobile;
            $ok = RateLimiter::attempt($bucket, $cfg['rate_limits']['otp_per_hour'], 3600);
            if (!$ok) {
                if ($request->wantsJson()) {
                    Response::json(['error' => authErrorMessage('rate_limited')], 429);
                } else {
                    Response::redirect('/subscribe?error=rate_limited&mobile=' . urlencode($mobile));
                }
                return false;
            }
            return true;
        }

        return true;
    }
}
