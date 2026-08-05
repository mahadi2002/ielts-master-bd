<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;

final class AdminUserController extends Controller
{
    public function __construct(private UserRepo $repo = new UserRepo())
    {
    }

    public function index(Request $request): Response
    {
        $last4 = $request->str('last4');

        return $this->view('admin/users/index', [
            'title' => 'Users',
            'users' => $last4 !== '' ? $this->repo->searchByLast4($last4) : $this->repo->recent(50),
            'last4' => $last4,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $user = $this->repo->find((int) $id);
        if ($user === null) {
            $this->notFound();
        }

        return $this->view('admin/users/show', [
            'title'   => 'User #' . $id,
            'user'    => $user,
            'charges' => (new SubscriptionRepo())->latestForUser((int) $id) !== null
                ? (new SubscriptionRepo())->chargesForSubscription((int) (new SubscriptionRepo())->latestForUser((int) $id)['id'])
                : [],
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $status = $request->str('status');
        if (in_array($status, ['pending', 'active', 'blocked'], true)) {
            $this->repo->setStatus((int) $id, $status);
            Session::notify('success', 'User status আপডেট হয়েছে।');
        }

        return $this->redirect('/admin/users/' . $id);
    }
}
