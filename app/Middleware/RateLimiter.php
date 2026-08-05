<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Exceptions\HttpException;

/**
 * Generic IP/user-keyed limiter used as `rl:<bucket>` in the route table.
 *
 * Buckets that also need an msisdn_hash key (otp_request, otp_verify) apply
 * that second key inside OtpService, where the number is actually known.
 */
final class RateLimiter implements Middleware
{
    public function __construct(private string $bucket)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = match ($this->bucket) {
            'qa_post' => 'user:' . (Session::userId() ?? 0),
            default   => 'ip:' . $request->ipHash(),
        };

        $retry = RateLimit::hit($this->bucket, $key);

        if ($retry !== null) {
            throw new HttpException(429, 'অনেকবার চেষ্টা হয়েছে। ' . RateLimit::humanWait($retry) . ' পর আবার চেষ্টা করুন।', [
                'retry_after' => $retry,
            ]);
        }

        return $next();
    }
}
