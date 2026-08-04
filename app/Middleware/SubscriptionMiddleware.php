<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Subscription;

final class SubscriptionMiddleware
{
    public function handle(Request $request): bool
    {
        $userId = Session::userId();
        $subscription = $userId ? Subscription::activeFor($userId) : null;

        if ($subscription === null) {
            Session::flashSet('message', 'আপনার Subscription-এর মেয়াদ শেষ হয়ে গেছে। আবার Subscribe করুন।');
            Session::flashSet('message_type', 'error');
            Response::redirect('/subscribe?expired=1');
            return false;
        }

        return true;
    }
}
