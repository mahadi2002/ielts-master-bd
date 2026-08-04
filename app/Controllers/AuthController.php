<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Crypto;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\Subscription;
use App\Models\User;
use App\Services\BdAppsGateway;
use App\Services\MockGateway;
use App\Services\SubscriptionGateway;

final class AuthController
{
    private array $config;

    public function __construct()
    {
        $this->config = require BASE_PATH . '/config.php';
    }

    public function mobileEntry(Request $request): void
    {
        if (Session::userId() && Subscription::activeFor((string) Session::userId())) {
            Response::redirect('/dashboard');
            return;
        }

        $mobile = (string) $request->query('mobile', '');

        if ($mobile !== '') {
            Response::html(View::render('auth/otp', [
                'mobile' => $mobile,
                'otpLength' => $this->config['otp']['length'],
                'resendCooldown' => $this->config['otp']['resend_cooldown'],
                'error' => authErrorMessage($request->query('error')),
                'debugOtp' => $this->config['app']['debug'] ? $request->query('debug_otp') : null,
            ]));
            return;
        }

        Response::html(View::render('auth/mobile-entry', [
            'expired' => $request->query('expired') === '1',
            'error' => authErrorMessage($request->query('error')),
            'oldMobile' => (string) $request->query('old_mobile', ''),
        ]));
    }

    public function sendOtp(Request $request): void
    {
        $mobile = trim((string) $request->input('mobile_number'));
        $operator = Validator::operatorForMobile($mobile);

        if ($operator === null) {
            if ($request->wantsJson()) {
                Response::json(['success' => false, 'error' => authErrorMessage('invalid_mobile')], 422);
                return;
            }
            Response::redirect('/subscribe?error=invalid_mobile&old_mobile=' . urlencode($mobile));
            return;
        }

        $otp = Crypto::otp($this->config['otp']['length']);
        $stmt = Db::pdo()->prepare(
            'INSERT INTO otp_requests (id, mobile_number, otp_hash, attempts, expires_at, created_at)
             VALUES (:id, :mobile, :hash, 0, :expires, NOW())'
        );
        $stmt->execute([
            'id' => Db::uuid(),
            'mobile' => $mobile,
            'hash' => Crypto::hash($otp),
            'expires' => date('Y-m-d H:i:s', time() + $this->config['otp']['ttl']),
        ]);

        // No live BDApps SDP/OTP SMS contract yet (known blocker) — log instead of sending.
        error_log("[OTP] {$mobile} => {$otp} (dev/mock delivery, TTL {$this->config['otp']['ttl']}s)");

        if ($request->wantsJson()) {
            Response::json([
                'success' => true,
                'ttl' => $this->config['otp']['ttl'],
                'resend_cooldown' => $this->config['otp']['resend_cooldown'],
                'debug_otp' => $this->config['app']['debug'] ? $otp : null,
            ]);
            return;
        }

        $redirect = '/subscribe?mobile=' . urlencode($mobile);
        if ($this->config['app']['debug']) {
            $redirect .= '&debug_otp=' . urlencode($otp);
        }
        Response::redirect($redirect);
    }

    public function verifyOtp(Request $request): void
    {
        $mobile = trim((string) $request->input('mobile_number'));
        $otp = trim((string) $request->input('otp'));

        $stmt = Db::pdo()->prepare(
            'SELECT * FROM otp_requests WHERE mobile_number = :mobile AND verified_at IS NULL
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['mobile' => $mobile]);
        $otpRequest = $stmt->fetch();

        $fail = function (string $code) use ($request, $mobile) {
            if ($request->wantsJson()) {
                Response::json(['success' => false, 'error' => authErrorMessage($code)], 422);
                return;
            }
            Response::redirect('/subscribe?mobile=' . urlencode($mobile) . '&error=' . $code);
        };

        if (!$otpRequest || strtotime($otpRequest['expires_at']) < time()) {
            $fail('expired');
            return;
        }

        if ((int) $otpRequest['attempts'] >= $this->config['otp']['max_attempts']) {
            $fail('too_many_attempts');
            return;
        }

        if (!Crypto::verify($otp, $otpRequest['otp_hash'])) {
            Db::pdo()->prepare('UPDATE otp_requests SET attempts = attempts + 1 WHERE id = :id')
                ->execute(['id' => $otpRequest['id']]);
            $fail('wrong_otp');
            return;
        }

        Db::pdo()->prepare('UPDATE otp_requests SET verified_at = NOW() WHERE id = :id')
            ->execute(['id' => $otpRequest['id']]);

        $operator = Validator::operatorForMobile($mobile) ?? 'robi';
        $user = User::findByMobile($mobile) ?? User::create($mobile, $operator);

        // Already subscribed and active → straight to dashboard, no duplicate charge (§8).
        $existing = Subscription::activeFor($user['id']);
        if ($existing) {
            Session::login($user['id']);
            Response::redirect('/dashboard');
            return;
        }

        $gateway = $this->gateway();
        $result = $gateway->subscribe($mobile, $this->config['subscription']['daily_amount']);

        if (!$result['success']) {
            Response::redirect('/subscribe?error=subscribe_failed');
            return;
        }

        $subscription = Subscription::create(
            $user['id'],
            $gateway->name(),
            $result['external_ref'],
            $this->config['subscription']['daily_amount']
        );
        Subscription::logEvent($subscription['id'], 'charge_success', $result);

        Session::login($user['id']);
        Response::redirect('/dashboard');
    }

    public function gatewayCallback(Request $request): void
    {
        // BDApps signs callbacks with BDAPPS_CALLBACK_SECRET — verify before trusting payload.
        if ($this->config['subscription']['gateway'] === 'bdapps') {
            $signature = $request->query('signature', '');
            $expected = hash_hmac('sha256', (string) $request->query('payload', ''), $this->config['subscription']['bdapps']['callback_secret']);
            if (!hash_equals($expected, (string) $signature)) {
                Response::json(['error' => 'invalid signature'], 403);
                return;
            }
        }
        Response::json(['received' => true]);
    }

    public function logout(Request $request): void
    {
        Session::logout();
        Response::redirect('/');
    }

    private function gateway(): SubscriptionGateway
    {
        return match ($this->config['subscription']['gateway']) {
            'bdapps' => new BdAppsGateway($this->config['subscription']['bdapps']),
            default => new MockGateway(),
        };
    }
}
