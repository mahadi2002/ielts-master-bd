<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Every payment integration point in the app talks to this interface only —
 * controllers must never call a gateway implementation directly.
 */
interface SubscriptionGateway
{
    /**
     * Charge/activate the daily subscription for a verified mobile number.
     * @return array{success: bool, external_ref: ?string, reason: ?string}
     */
    public function subscribe(string $mobileNumber, float $dailyAmount): array;

    /**
     * Poll or verify current charge status for a subscription.
     * @return array{active: bool, external_ref: ?string}
     */
    public function checkStatus(string $externalRef): array;

    public function cancel(string $externalRef): bool;

    public function name(): string;
}
