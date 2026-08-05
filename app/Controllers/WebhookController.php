<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\HttpException;
use App\Services\GatewayFactory;

/**
 * BDApps async callbacks (charge settlement, STOP-SMS unsubscribe). No CSRF
 * here — the caller is BDApps, not a browser with our session cookie.
 * Authenticity comes from the signature check plus (optionally) an IP
 * allowlist, both inside the gateway implementation.
 *
 * The event is only recorded and queued here, never applied inline — a slow
 * synchronous state-machine write would make BDApps time out and retry the
 * same event, and this endpoint needs to answer 200 fast either way.
 * cron/queue_worker.php drains the actual state transition.
 */
final class WebhookController extends Controller
{
    public function bdapps(Request $request): Response
    {
        $raw = $request->rawBody();

        if (!GatewayFactory::make()->verifyWebhook($raw, $request->headers)) {
            throw new HttpException(403, 'invalid signature');
        }

        $payload = json_decode($raw, true);
        $eventId = is_array($payload) ? (string) ($payload['event_id'] ?? '') : '';

        if ($eventId === '') {
            throw new HttpException(422, 'missing event_id');
        }

        // Idempotency: BDApps may retry a webhook it thinks failed.
        $already = Db::value('SELECT id FROM webhook_events WHERE event_id = ?', [$eventId]);
        if ($already !== null) {
            return $this->json(['received' => true, 'duplicate' => true]);
        }

        Db::insert(
            'INSERT INTO webhook_events (event_id, type, payload, signature_ok, created_at) VALUES (?, ?, ?, 1, NOW())',
            [$eventId, (string) ($payload['type'] ?? ''), (string) json_encode($payload, JSON_UNESCAPED_UNICODE)]
        );

        Db::insert(
            'INSERT INTO jobs (queue, job, payload, available_at) VALUES ("default", "webhook.apply", ?, NOW())',
            [(string) json_encode(['event_id' => $eventId], JSON_UNESCAPED_UNICODE)]
        );

        return $this->json(['received' => true]);
    }
}
