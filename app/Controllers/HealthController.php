<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;

/**
 * What an uptime monitor (UptimeRobot, a load balancer health check, etc.)
 * hits. Actually touches the database rather than just returning 200 —
 * "PHP is running" and "the app can serve a request" are two different
 * claims, and this one checks the claim that actually matters.
 */
final class HealthController extends Controller
{
    public function check(Request $request): Response
    {
        try {
            Db::value('SELECT 1');
        } catch (\Throwable $e) {
            return $this->json(['status' => 'down', 'db' => false], 503);
        }

        return $this->json(['status' => 'ok', 'db' => true, 'time' => date(DATE_ATOM)]);
    }
}
