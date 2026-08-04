<?php

declare(strict_types=1);

/**
 * Polls subscriptions due for a renewal/charge-status check via the
 * configured SubscriptionGateway (never called inline from a controller).
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\Subscription;
use App\Services\BdAppsGateway;
use App\Services\MockGateway;
use App\Services\SubscriptionGateway;

function makeGateway(array $config): SubscriptionGateway
{
    return match ($config['subscription']['gateway']) {
        'bdapps' => new BdAppsGateway($config['subscription']['bdapps']),
        default => new MockGateway(),
    };
}

$config = require dirname(__DIR__) . '/config.php';
$gateway = makeGateway($config);

$due = Subscription::dueForChargeCheck();
$renewed = 0;
$failed = 0;

foreach ($due as $subscription) {
    try {
        $status = $gateway->checkStatus((string) $subscription['external_ref']);
    } catch (\Throwable $e) {
        error_log('[subscription-check] gateway error for ' . $subscription['id'] . ': ' . $e->getMessage());
        continue;
    }

    if ($status['active']) {
        \App\Core\Db::pdo()->prepare(
            'UPDATE subscriptions SET last_charged_at = NOW(), next_charge_at = :next WHERE id = :id'
        )->execute([
            'next' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'id' => $subscription['id'],
        ]);
        Subscription::logEvent($subscription['id'], 'renewed', $status);
        $renewed++;
    } else {
        Subscription::expire($subscription['id']);
        Subscription::logEvent($subscription['id'], 'charge_failed', $status);
        $failed++;
    }
}

echo "[subscription-check] " . date('Y-m-d H:i:s') . " — checked: " . count($due) . ", renewed: {$renewed}, failed: {$failed}\n";
