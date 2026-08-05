<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\ContactRepo;
use App\Services\AuditService;

final class AdminContactController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->str('status', 'new');
        if (!in_array($status, ['new', 'read', 'resolved', 'all'], true)) {
            $status = 'new';
        }

        $repo = new ContactRepo();

        return $this->view('admin/contact/index', [
            'title'    => 'Contact Inbox',
            'messages' => $repo->queue($status),
            'status'   => $status,
            'newCount' => $repo->newCount(),
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $repo    = new ContactRepo();
        $message = $repo->find((int) $id);

        if ($message === null) {
            $this->notFound();
        }

        if ($message['status'] === 'new') {
            $repo->markRead((int) $id);
            $message['status'] = 'read';
        }

        return $this->view('admin/contact/show', ['title' => 'Message #' . $id, 'message' => $message]);
    }

    public function resolve(Request $request, string $id): Response
    {
        $repo    = new ContactRepo();
        $message = $repo->find((int) $id);

        if ($message === null) {
            $this->notFound();
        }

        $adminId = (int) $this->currentUserId();
        $repo->resolve((int) $id, $adminId);

        AuditService::log('admin.contact.resolved', 'admin', $adminId, 'contact', (int) $id, [], $request->ipHash());

        Session::notify('success', 'সমাধান করা হয়েছে বলে চিহ্নিত করা হলো।');
        return $this->redirect('/admin/contact');
    }
}
