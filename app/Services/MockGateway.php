<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Db;

/**
 * Dev/test stand-in for BdAppsGateway. Always succeeds so the whole product
 * can be built and tested before real BDApps credentials arrive.
 */
final class MockGateway implements SubscriptionGateway
{
    public function subscribe(string $mobileNumber, float $dailyAmount): array
    {
        return [
            'success' => true,
            'external_ref' => 'MOCK-' . Db::uuid(),
            'reason' => null,
        ];
    }

    public function checkStatus(string $externalRef): array
    {
        return ['active' => true, 'external_ref' => $externalRef];
    }

    public function cancel(string $externalRef): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'mock';
    }
}
