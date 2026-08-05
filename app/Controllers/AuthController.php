<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\GatewayException;
use App\Exceptions\OtpException;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\OtpService;
use App\Services\SubscriptionService;
use App\Support\Operators;

/**
 * The subscribe funnel. Every OTP failure case gets its own exact Bangla
 * message here — "wrong code" and "too many tries" read very differently
 * to someone who's never used an OTP flow before.
 */
final class AuthController extends Controller
{
    private const PENDING_KEY = '_pending_msisdn';
    private const STARTED_KEY = '_otp_form_started';

    public function phoneForm(Request $request): Response
    {
        return $this->renderPhoneForm($request, false);
    }

    public function requestOtp(Request $request): Response
    {
        // Honeypot + minimum fill time — cheaper and kinder than a CAPTCHA.
        if ($request->str('website') !== '') {
            return $this->redirect('/subscribe');
        }

        $started = (int) Session::get(self::STARTED_KEY, 0);
        if ($started > 0 && time() - $started < 2) {
            Session::notify('error', 'একটু ধীরে চেষ্টা করুন।');
            return $this->redirect('/subscribe');
        }

        $validator = Validator::make($request->body, ['msisdn' => 'required|msisdn'], ['msisdn' => 'মোবাইল নম্বর']);

        if ($validator->fails()) {
            $validator->flash();
            return $this->redirect('/subscribe');
        }

        $msisdn = (string) Operators::normalize((string) $validator->get('msisdn'));

        if (!Operators::isAllowed($msisdn)) {
            Session::flash('_errors', ['msisdn' => ['এই Service এখন শুধু Robi ও Airtel Number-এ পাওয়া যাচ্ছে।']]);
            Session::flash('_old', ['msisdn' => $msisdn]);
            return $this->redirect('/subscribe');
        }

        return $this->sendOtp($msisdn, $request, false);
    }

    public function otpForm(Request $request): Response
    {
        $msisdn = (string) Session::get(self::PENDING_KEY, '');

        if ($msisdn === '') {
            return $this->redirect('/subscribe');
        }

        return $this->view('auth/otp', [
            'masked'     => Operators::mask($msisdn),
            'resendWait' => OtpService::make()->resendWaitFor($msisdn),
            'devOtp'     => config('app.debug') ? Session::flashGet('_dev_otp') : null,
            'next'       => $this->safeNext((string) Session::get('_next', '')),
        ]);
    }

    public function verifyOtp(Request $request): Response
    {
        $msisdn = (string) Session::get(self::PENDING_KEY, '');

        if ($msisdn === '') {
            return $this->redirect('/subscribe');
        }

        $validator = Validator::make($request->body, ['code' => 'required|digits:6'], ['code' => 'OTP Code']);

        if ($validator->fails()) {
            $validator->flash();
            return $this->redirect('/subscribe/verify');
        }

        try {
            $subscriberRef = OtpService::make()->verify($msisdn, (string) $validator->get('code'), $request);
        } catch (OtpException $e) {
            Session::flash('_errors', ['code' => [$e->getMessage()]]);

            if ($e->reason === 'too_many' || $e->reason === 'expired' || $e->reason === 'missing') {
                Session::forget(self::PENDING_KEY);
                Session::notify('error', $e->getMessage());
                return $this->redirect('/subscribe');
            }

            return $this->redirect('/subscribe/verify');
        } catch (GatewayException $e) {
            \App\Core\Logger::warning('otp.verify.gateway_failed', ['error' => $e->getMessage()]);
            Session::notify('error', 'এই মুহূর্তে যাচাই করা যাচ্ছে না। একটু পরে আবার চেষ্টা করুন।');
            return $this->redirect('/subscribe/verify');
        }

        return $this->completeSubscribe($msisdn, $subscriberRef, $request);
    }

    public function resendOtp(Request $request): Response
    {
        $msisdn = (string) Session::get(self::PENDING_KEY, '');

        if ($msisdn === '') {
            return $this->redirect('/subscribe');
        }

        $wait = OtpService::make()->resendWaitFor($msisdn);
        if ($wait > 0) {
            Session::notify('error', bn_num((int) config('app.otp.resend_cooldown', 60)) . ' সেকেন্ড পর আবার পাঠাতে পারবেন।');
            return $this->redirect('/subscribe/verify');
        }

        return $this->sendOtp($msisdn, $request, true);
    }

    /**
     * GET /login — same OTP mechanism as /subscribe (a returning subscriber's
     * number is recognised and logged straight in — see completeSubscribe()),
     * but framed as a login screen for people already paying who just want
     * back in.
     */
    public function login(Request $request): Response
    {
        return $this->renderPhoneForm($request, true);
    }

    public function logout(Request $request): Response
    {
        $userId = Session::userId();

        if ($userId !== null) {
            AuditService::log('auth.logout', 'user', $userId, null, null, [], $request->ipHash());
        }

        Session::destroy_all();

        return $this->redirect('/');
    }

    // ── internals ───────────────────────────────────────────────────────

    private function renderPhoneForm(Request $request, bool $isLogin): Response
    {
        Session::put(self::STARTED_KEY, time());

        return $this->view('auth/phone', [
            'next'    => $this->safeNext($request->str('next')),
            'isLogin' => $isLogin,
        ]);
    }

    private function sendOtp(string $msisdn, Request $request, bool $isResend): Response
    {
        try {
            OtpService::make()->send($msisdn, $request, $isResend);
        } catch (OtpException $e) {
            Session::flash('_errors', ['msisdn' => [$e->getMessage()]]);
            Session::flash('_old', ['msisdn' => $msisdn]);
            Session::notify('error', $e->getMessage());

            return $this->redirect($isResend ? '/subscribe/verify' : '/subscribe');
        } catch (GatewayException $e) {
            \App\Core\Logger::warning('otp.send.gateway_failed', ['error' => $e->getMessage()]);
            Session::notify('error', 'এই মুহূর্তে OTP পাঠানো যাচ্ছে না। একটু পরে আবার চেষ্টা করুন।');

            return $this->redirect($isResend ? '/subscribe/verify' : '/subscribe')->withStatus(503);
        }

        Session::put(self::PENDING_KEY, $msisdn);
        Session::put('_next', $this->safeNext($request->str('next')));

        return $this->redirect('/subscribe/verify');
    }

    /**
     * OTP verified. Create-or-reuse the user, open a pending subscription and
     * attempt the first charge immediately — the user is watching the screen,
     * so waiting an hour for cron would be a terrible first impression.
     */
    private function completeSubscribe(string $msisdn, string $subscriberRef, Request $request): Response
    {
        $userRepo = new UserRepo();
        $user     = $userRepo->findOrCreate($msisdn);
        $userId   = (int) $user['id'];

        if ($user['status'] === 'blocked') {
            Session::forget(self::PENDING_KEY);
            Session::notify('error', 'এই নম্বর দিয়ে এখন Login করা যাচ্ছে না। যোগাযোগ করুন।');
            return $this->redirect('/contact');
        }

        $service = new SubscriptionService();
        $subRepo = new SubscriptionRepo();

        // Session fixation: rotate the id the moment the identity changes.
        Session::regenerate();
        Session::put('user_id', $userId);
        Session::put('_ua', $request->uaHash());
        Session::forget(self::PENDING_KEY);
        Session::forget(self::STARTED_KEY);

        $userRepo->touchLogin($userId);

        AuditService::log('auth.otp_verified', 'user', $userId, 'user', $userId, [
            'msisdn_last4' => Operators::last4($msisdn),
        ], $request->ipHash(), $request->uaHash());

        // Already an active subscriber — just let them in.
        if (SubscriptionService::hasAccess($userId)) {
            Session::notify('success', 'আপনি আগে থেকেই Subscribed। স্বাগতম!');
            return $this->redirect($this->safeNext((string) Session::get('_next', '')) ?: '/app');
        }

        $subscriptionId = $service->startPending($userId, $subscriberRef);
        $result         = $this->chargeFirstPeriod($subscriptionId, $subscriberRef, $service, $subRepo);

        if ($result !== 'success') {
            Session::notify('error', 'ব্যালেন্স কম মনে হচ্ছে। Recharge করে আবার চেষ্টা করুন।');
            return $this->redirect('/expired');
        }

        Session::notify('success', 'স্বাগতম! আপনার Subscription চালু হয়েছে।');

        return $this->redirect($this->safeNext((string) Session::get('_next', '')) ?: '/app');
    }

    private function chargeFirstPeriod(
        int $subscriptionId,
        string $subscriberRef,
        SubscriptionService $service,
        SubscriptionRepo $repo,
    ): string {
        $amount = (float) config('bdapps.amount', 2.78);
        $key    = sha1($subscriptionId . ':' . date('Y-m-d'));

        if (!$repo->claimCharge($subscriptionId, $key, $amount)) {
            // Already attempted today — trust whatever that attempt concluded.
            return SubscriptionService::hasAccess((int) $repo->userIdFor($subscriptionId)) ? 'success' : 'failed';
        }

        try {
            $charge = \App\Services\GatewayFactory::make()->charge($subscriberRef, $key, $amount);
        } catch (\Throwable $e) {
            \App\Core\Logger::warning('charge.first.transport_error', ['error' => $e->getMessage()]);
            $repo->settleCharge($key, 'failed', null, 'TRANSPORT', [], $e->getMessage());
            return 'failed';
        }

        $repo->settleCharge(
            $key,
            $charge['status'] === 'success' ? 'success' : ($charge['status'] === 'pending' ? 'pending' : 'failed'),
            $charge['txn_id'],
            $charge['code'],
            $charge['raw'],
            $charge['status'] === 'success' ? null : (string) ($charge['code'] ?? 'failed')
        );

        if ($charge['status'] === 'success') {
            $service->activate($subscriptionId);
            return 'success';
        }

        AuditService::log('charge.first_failed', 'gateway', null, 'subscription', $subscriptionId, [
            'code' => $charge['code'],
        ]);

        return 'failed';
    }

    /** Only same-site absolute paths may be used as a post-login redirect. */
    private function safeNext(string $next): string
    {
        if ($next === '' || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return '';
        }
        return preg_match('#^/[A-Za-z0-9/_\-]{0,140}$#', $next) === 1 ? $next : '';
    }
}
