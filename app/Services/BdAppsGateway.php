<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Stub. Real BDApps SDP/OTP charge endpoints, params, and callback signature
 * are a known blocker (see 01-BUILD-SPEC.md §9.1) — do not hardcode guessed
 * endpoints here. Wire this up once BDApps dashboard access is available.
 */
final class BdAppsGateway implements SubscriptionGateway
{
    public function __construct(private array $config)
    {
    }

    public function subscribe(string $mobileNumber, float $dailyAmount): array
    {
        throw new \RuntimeException(
            'BdAppsGateway is not implemented yet — BDApps API contract not received. ' .
            'Set SUBSCRIPTION_GATEWAY=mock in .env until then.'
        );
    }

    public function checkStatus(string $externalRef): array
    {
        throw new \RuntimeException('BdAppsGateway::checkStatus not implemented — see BDApps blocker.');
    }

    public function cancel(string $externalRef): bool
    {
        throw new \RuntimeException('BdAppsGateway::cancel not implemented — see BDApps blocker.');
    }

    public function name(): string
    {
        return 'bdapps';
    }
}
