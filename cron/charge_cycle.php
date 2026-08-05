<?php
declare(strict_types=1);

/**
 * Hourly billing cycle.
 *
 * Cron entry (cPanel):
 *   0 * * * *  /usr/local/bin/php /home/USER/ieltsmasterbd/cron/charge_cycle.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Logger;
use App\Repositories\SubscriptionRepo;
use App\Services\AuditService;
use App\Services\GatewayFactory;
use App\Services\SubscriptionService;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('charge_cycle');
if ($release === null) {
    exit(0);
}

$start   = microtime(true);
$hardCap = 15 * 60;

$repo    = new SubscriptionRepo();
$service = new SubscriptionService($repo);
$gateway = GatewayFactory::make();

$due       = $repo->dueForCharge(200);
$processed = 0;

foreach ($due as $sub) {
    if (microtime(true) - $start > $hardCap) {
        Logger::warning('charge_cycle.hard_cap_reached', ['processed' => $processed]);
        break;
    }

    $subscriptionId = (int) $sub['id'];
    $key            = sha1($subscriptionId . ':' . date('Y-m-d'));
    $amount         = (float) $sub['daily_amount'];

    if (!$repo->claimCharge($subscriptionId, $key, $amount)) {
        // Already attempted today by an earlier run of this cron.
        continue;
    }

    if ($sub['bdapps_sub_ref'] === null) {
        $repo->settleCharge($key, 'failed', null, 'NO_REF', [], 'missing subscriber ref');
        continue;
    }

    try {
        $result = $gateway->charge((string) $sub['bdapps_sub_ref'], $key, $amount);
    } catch (\Throwable $e) {
        Logger::warning('charge_cycle.transport_error', ['sub_id' => $subscriptionId, 'error' => $e->getMessage()]);
        $repo->settleCharge($key, 'failed', null, 'TRANSPORT', [], $e->getMessage());
        $result = ['status' => 'failed', 'txn_id' => null, 'code' => 'TRANSPORT', 'raw' => []];
    }

    $repo->settleCharge(
        $key,
        $result['status'] === 'success' ? 'success' : ($result['status'] === 'pending' ? 'pending' : 'failed'),
        $result['txn_id'] ?? null,
        $result['code'] ?? null,
        $result['raw'] ?? [],
        $result['status'] === 'success' ? null : (string) ($result['code'] ?? 'failed')
    );

    if ($result['status'] === 'success') {
        // renew() measures the new period from the OLD period end, not NOW() —
        // otherwise cron drift steals minutes from the user every single day.
        $service->renew($subscriptionId);
    } elseif ($result['status'] === 'pending') {
        // Leave the subscription state untouched; a webhook or the next cycle
        // will resolve it. Do not start a grace clock for a pending charge.
        AuditService::log('charge.pending', 'gateway', null, 'subscription', $subscriptionId);
    } else {
        $inGraceAlready = $sub['status'] === 'grace';
        $graceUntil     = $sub['grace_until'] !== null ? strtotime((string) $sub['grace_until']) : null;

        if ($inGraceAlready && $graceUntil !== null && $graceUntil <= time()) {
            $service->expire($subscriptionId, (string) ($result['code'] ?? 'charge_failed'));
        } else {
            $service->toGrace($subscriptionId, (string) ($result['code'] ?? 'charge_failed'));
        }
    }

    $processed++;

    // Rate courtesy toward the gateway.
    usleep(200_000);
}

Logger::info('charge_cycle.done', ['processed' => $processed, 'seconds' => round(microtime(true) - $start, 1)]);
fwrite(STDOUT, "charge_cycle: processed $processed subscription(s).\n");

$release();
