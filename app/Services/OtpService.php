<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Crypto;
use App\Core\Db;
use App\Core\RateLimit;
use App\Core\Request;
use App\Exceptions\GatewayException;
use App\Exceptions\OtpException;
use App\Support\Operators;

/**
 * OTP lifecycle:
 *   6 digits from random_int(), stored as password_hash(), never plaintext,
 *   never logged in production. TTL 5 min, single use, 3 wrong attempts kills
 *   the row, resend allowed after 60 s.
 *
 * When the gateway returns a `code` (MockGateway) the comparison is local.
 * When it does not (BdAppsGateway) verification is delegated to the gateway
 * and the stored hash is only used for attempt counting.
 */
final class OtpService
{
    public function __construct(
        private SubscriptionGateway $gateway = new MockGateway(),
    ) {
    }

    public static function make(): self
    {
        return new self(GatewayFactory::make());
    }

    /**
     * @return array{reference:string, expires_at:int}
     * @throws OtpException on throttle, GatewayException on transport failure
     */
    public function send(string $msisdn, Request $request, bool $isResend = false): array
    {
        $msisdnHash = Crypto::blindIndex($msisdn);

        // Per-number throttle. The route middleware already applied the per-IP
        // half of the bucket — both need to pass, otherwise one shared IP
        // (an office, a family data plan) locks out everyone behind it.
        $retry = RateLimit::hit('otp_request', 'msisdn:' . $msisdnHash);
        if ($retry !== null) {
            throw new OtpException(
                'rate_limited',
                'অনেকবার চেষ্টা হয়েছে। ' . RateLimit::humanWait($retry) . ' পর আবার চেষ্টা করুন।'
            );
        }

        // 60 s resend cooldown.
        $last = Db::first(
            'SELECT created_at FROM otp_requests WHERE msisdn_hash = ? ORDER BY id DESC LIMIT 1',
            [$msisdnHash]
        );
        if ($last !== null) {
            $cooldown = (int) config('app.otp.resend_cooldown', 60);
            $elapsed  = time() - strtotime((string) $last['created_at']);
            if ($elapsed < $cooldown) {
                throw new OtpException(
                    'cooldown',
                    bn_num($cooldown) . ' সেকেন্ড পর আবার পাঠাতে পারবেন।'
                );
            }
        }

        try {
            $sent = $this->gateway->sendOtp($msisdn);
        } catch (OtpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new GatewayException($e->getMessage(), 0, $e);
        }

        // A gateway that owns the code (production) gives us nothing to hash;
        // store an unguessable placeholder so the row shape stays uniform.
        $code = (string) ($sent['code'] ?? Crypto::randomToken(16));

        // Invalidate any earlier live code for this number — one at a time.
        Db::exec(
            'UPDATE otp_requests SET consumed_at = NOW()
             WHERE msisdn_hash = ? AND consumed_at IS NULL',
            [$msisdnHash]
        );

        Db::insert(
            'INSERT INTO otp_requests
                (msisdn_hash, otp_hash, reference_id, purpose, max_attempts, ip_hash, ua_hash, expires_at)
             VALUES (?, ?, ?, "subscribe", ?, ?, ?, FROM_UNIXTIME(?))',
            [
                $msisdnHash,
                password_hash($code, PASSWORD_DEFAULT),
                (string) $sent['ref'],
                (int) config('app.otp.max_attempts', 3),
                $request->ipHash(),
                $request->uaHash(),
                (int) $sent['expires_at'],
            ]
        );

        AuditService::log('otp.sent', 'system', null, 'otp', null, [
            'msisdn_last4' => Operators::last4($msisdn),
            'hash_prefix'  => substr($msisdnHash, 0, 8),
            'resend'       => $isResend,
        ], $request->ipHash(), $request->uaHash());

        return ['reference' => (string) $sent['ref'], 'expires_at' => (int) $sent['expires_at']];
    }

    /**
     * @return string subscriber_ref from the gateway
     * @throws OtpException with a Bangla message specific to what went wrong
     */
    public function verify(string $msisdn, string $code, Request $request): string
    {
        $msisdnHash = Crypto::blindIndex($msisdn);

        $retry = RateLimit::hit('otp_verify', 'msisdn:' . $msisdnHash);
        if ($retry !== null) {
            throw new OtpException(
                'rate_limited',
                'অনেকবার চেষ্টা হয়েছে। ' . RateLimit::humanWait($retry) . ' পর আবার চেষ্টা করুন।'
            );
        }

        $row = Db::first(
            'SELECT id, otp_hash, reference_id, attempts, max_attempts, expires_at
             FROM otp_requests
             WHERE msisdn_hash = ? AND consumed_at IS NULL
             ORDER BY id DESC LIMIT 1',
            [$msisdnHash]
        );

        if ($row === null) {
            throw new OtpException('missing', 'Code-এর মেয়াদ শেষ। নতুন Code নিন।');
        }

        if (strtotime((string) $row['expires_at']) <= time()) {
            Db::exec('UPDATE otp_requests SET consumed_at = NOW() WHERE id = ?', [$row['id']]);
            throw new OtpException('expired', 'Code-এর মেয়াদ শেষ। নতুন Code নিন।');
        }

        $isDevCode = config('bdapps.driver') === 'mock' && $code === MockGateway::DEV_CODE;
        $matches   = $isDevCode || password_verify($code, (string) $row['otp_hash']);

        if (!$matches) {
            $attempts = (int) $row['attempts'] + 1;
            $max      = (int) $row['max_attempts'];

            if ($attempts >= $max) {
                Db::exec('UPDATE otp_requests SET attempts = ?, consumed_at = NOW() WHERE id = ?', [$attempts, $row['id']]);
                AuditService::log('otp.failed_final', 'system', null, 'otp', (int) $row['id'], [
                    'hash_prefix' => substr($msisdnHash, 0, 8),
                ], $request->ipHash());

                throw new OtpException('too_many', 'Code ৩ বার ভুল হয়েছে। নতুন করে OTP নিন।', 0);
            }

            Db::exec('UPDATE otp_requests SET attempts = ? WHERE id = ?', [$attempts, $row['id']]);
            $left = $max - $attempts;

            throw new OtpException(
                'invalid',
                'Code মিলছে না। আর ' . bn_num($left) . ' বার চেষ্টা করতে পারবেন।',
                $left
            );
        }

        // Correct code — burn it before anything else can race on it.
        Db::exec('UPDATE otp_requests SET consumed_at = NOW() WHERE id = ?', [$row['id']]);
        RateLimit::reset('otp_verify', 'msisdn:' . $msisdnHash);

        $result = $this->gateway->verifyOtp((string) $row['reference_id'], $code, $msisdn);

        return (string) $result['subscriber_ref'];
    }

    /** Seconds until a resend is permitted, or 0. */
    public function resendWaitFor(string $msisdn): int
    {
        $last = Db::first(
            'SELECT created_at FROM otp_requests WHERE msisdn_hash = ? ORDER BY id DESC LIMIT 1',
            [Crypto::blindIndex($msisdn)]
        );
        if ($last === null) {
            return 0;
        }
        $cooldown = (int) config('app.otp.resend_cooldown', 60);
        return max(0, $cooldown - (time() - strtotime((string) $last['created_at'])));
    }
}
