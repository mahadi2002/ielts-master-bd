<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\SubscriptionService;

/**
 * Reachable while a subscription is pending/expired — unsubscribing must
 * never be gated behind "you have to be an active paying customer first".
 */
final class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $user   = (new UserRepo())->find($userId);
        $sub    = SubscriptionService::current($userId);

        return $this->view('account/index', [
            'title' => 'Account',
            'user'  => $user,
            'sub'   => $sub,
        ]);
    }

    public function unsubscribeForm(Request $request): Response
    {
        return $this->view('account/unsubscribe', ['title' => 'Unsubscribe']);
    }

    public function unsubscribe(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $sub    = (new SubscriptionRepo())->latestForUser($userId);

        if ($sub !== null && !in_array((string) $sub['status'], ['unsubscribed', 'expired'], true)) {
            (new SubscriptionService())->unsubscribe((int) $sub['id'], 'user', $request->str('reason'));
        }

        Session::destroy_all();
        Session::notify('success', 'আপনার Subscription বন্ধ করা হয়েছে। আবার Subscribe করতে চাইলে যেকোনো সময় আসুন।');

        return $this->redirect('/');
    }

    public function destroy(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $sub    = (new SubscriptionRepo())->latestForUser($userId);

        if ($sub !== null && !in_array((string) $sub['status'], ['unsubscribed', 'expired'], true)) {
            (new SubscriptionService())->unsubscribe((int) $sub['id'], 'user', 'account_deleted');
        }

        (new UserRepo())->anonymize($userId);
        AuditService::log('account.deleted', 'user', $userId, 'user', $userId);

        Session::destroy_all();
        Session::notify('success', 'আপনার Account মুছে ফেলা হয়েছে।');

        return $this->redirect('/');
    }

    public function expired(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $sub    = SubscriptionService::current($userId);

        return $this->view('account/expired', ['title' => 'Subscription Expired', 'sub' => $sub]);
    }
}
