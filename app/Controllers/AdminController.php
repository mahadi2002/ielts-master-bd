<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\Word;

final class AdminController
{
    public function __construct()
    {
        $user = Session::user();
        if (!$user || !User::isAdmin($user)) {
            http_response_code(403);
            echo View::render('errors/403');
            exit;
        }
    }

    public function dashboard(Request $request): void
    {
        Response::redirect('/admin/exclusive-pool');
    }

    public function words(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $words = Word::paginate(50, ($page - 1) * 50);

        Response::html(View::render('admin/words', [
            'words' => $words,
            'page' => $page,
            'total' => Word::countAll(),
        ]));
    }

    public function wordsStore(Request $request): void
    {
        $synonyms = array_filter(array_map('trim', explode(',', (string) $request->input('synonyms', ''))));

        Word::create([
            'headword' => (string) $request->input('headword'),
            'ipa' => $request->input('ipa') ?: null,
            'part_of_speech' => $request->input('part_of_speech') ?: null,
            'definition_en' => (string) $request->input('definition_en'),
            'definition_bn' => $request->input('definition_bn') ?: null,
            'example_sentence' => $request->input('example_sentence') ?: null,
            'synonyms' => $synonyms ?: null,
            'ielts_band_level' => (int) $request->input('ielts_band_level', 7),
            'task_tag' => $request->input('task_tag') ?: null,
            'is_exclusive' => (bool) $request->input('is_exclusive'),
            'frequency_rank' => $request->input('frequency_rank') !== '' ? (int) $request->input('frequency_rank') : null,
        ]);

        Session::flashSet('message', 'শব্দ যোগ করা হয়েছে।');
        Session::flashSet('message_type', 'success');
        Response::redirect('/admin/words');
    }

    public function wordsDelete(Request $request): void
    {
        Word::delete((string) $request->param('id'));
        Session::flashSet('message', 'শব্দ মুছে ফেলা হয়েছে।');
        Session::flashSet('message_type', 'success');
        Response::redirect('/admin/words');
    }

    public function users(Request $request): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $users = User::paginate(50, ($page - 1) * 50);

        Response::html(View::render('admin/users', [
            'users' => $users,
            'page' => $page,
            'total' => User::countAll(),
        ]));
    }

    public function exclusivePool(Request $request): void
    {
        $pools = Db::pdo()->query(
            "SELECT ielts_band_level band,
                    SUM(CASE WHEN is_exclusive THEN 1 ELSE 0 END) total,
                    SUM(CASE WHEN is_exclusive AND id NOT IN (SELECT word_id FROM user_collection) THEN 1 ELSE 0 END) unclaimed
             FROM words WHERE is_exclusive = TRUE GROUP BY ielts_band_level"
        )->fetchAll();

        $alerts = Db::pdo()->query(
            "SELECT * FROM admin_alerts WHERE resolved = FALSE ORDER BY created_at DESC"
        )->fetchAll();

        Response::html(View::render('admin/exclusive-pool', ['pools' => $pools, 'alerts' => $alerts]));
    }
}
