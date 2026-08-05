<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\AdminAlertRepo;
use App\Repositories\QuestionRepo;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;
use App\Repositories\WordRepo;

final class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $subRepo = new SubscriptionRepo();

        $pools = Db::all(
            "SELECT ielts_band_level AS band,
                    SUM(CASE WHEN is_exclusive THEN 1 ELSE 0 END) AS total,
                    SUM(CASE WHEN is_exclusive AND id NOT IN (SELECT word_id FROM user_collection) THEN 1 ELSE 0 END) AS unclaimed
               FROM words WHERE is_exclusive = 1 GROUP BY ielts_band_level"
        );

        return $this->view('admin/dashboard', [
            'title'          => 'Admin Dashboard',
            'wordCount'      => (new WordRepo())->countAll(),
            'userCount'      => (new UserRepo())->countActive(),
            'newUsers7d'     => (new UserRepo())->countNewSince(7),
            'openQuestions'  => (new QuestionRepo())->countOpen(),
            'subStatuses'    => $subRepo->statusBreakdown(),
            'chargesToday'   => $subRepo->chargeTotalsToday(),
            'recentFailures' => $subRepo->recentFailures(10),
            'pools'          => $pools,
            'alerts'         => (new AdminAlertRepo())->unresolved(20),
        ]);
    }

    public function logs(Request $request): Response
    {
        $page  = max(1, $request->int('page', 1));
        $limit = 50;

        $entries = Db::all(
            'SELECT * FROM audit_log ORDER BY id DESC LIMIT ' . $limit . ' OFFSET ' . (($page - 1) * $limit)
        );

        return $this->view('admin/logs', ['title' => 'Audit Log', 'entries' => $entries, 'page' => $page]);
    }
}
