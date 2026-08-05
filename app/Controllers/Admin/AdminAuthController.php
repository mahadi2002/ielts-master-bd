<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuditService;

/** Staff sign-in is email + password (ARGON2ID), deliberately separate from the OTP flow end users go through. */
final class AdminAuthController extends Controller
{
    public function form(Request $request): Response
    {
        if (Session::adminId() !== null) {
            return $this->redirect('/admin');
        }
        return $this->view('admin/login', ['title' => 'Admin Login']);
    }

    public function login(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'email'    => 'required|email',
            'password' => 'required',
        ], ['email' => 'Email', 'password' => 'Password']);

        if ($validator->fails()) {
            $validator->flash(['_token', 'password']);
            return $this->redirect('/admin/login');
        }

        $admin = Db::first('SELECT * FROM admins WHERE email = ?', [$validator->get('email')]);

        if ($admin === null || (int) $admin['is_active'] !== 1 || !password_verify((string) $validator->get('password'), (string) $admin['password_hash'])) {
            AuditService::log('admin.login_failed', 'admin', null, null, null, [
                'email' => (string) $validator->get('email'),
            ], $request->ipHash());

            Session::flash('_errors', ['password' => ['Email অথবা Password ভুল।']]);
            return $this->redirect('/admin/login');
        }

        Session::regenerate();
        Session::put('admin_id', (int) $admin['id']);

        Db::exec('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
        AuditService::log('admin.login', 'admin', (int) $admin['id'], 'admin', (int) $admin['id'], [], $request->ipHash());

        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $adminId = Session::adminId();
        if ($adminId !== null) {
            AuditService::log('admin.logout', 'admin', $adminId, null, null, [], $request->ipHash());
        }
        Session::forget('admin_id');
        return $this->redirect('/admin/login');
    }
}
