<?php
declare(strict_types=1);

namespace App\Services;

/**
 * The only contract the application knows about. No other file in the project
 * is aware that BDApps exists — swap the driver, nothing else moves.
 */
interface SubscriptionGateway
{
    /** @return array{ref:string, expires_at:int} */
    public function sendOtp(string $msisdn): array;

    /**
     * @return array{subscriber_ref:string}
     * @throws \App\Exceptions\OtpException
     */
    public function verifyOtp(string $ref, string $code, string $msisdn): array;

    /** @return array{status:'success'|'failed'|'pending', txn_id:?string, code:?string, raw:array} */
    public function charge(string $subscriberRef, string $idempotencyKey, float $amount): array;

    /** @return array{status:string, raw:array} */
    public function status(string $subscriberRef): array;

    public function unsubscribe(string $subscriberRef): bool;

    /** Verify an inbound webhook (signature and/or IP allowlist). */
    public function verifyWebhook(string $rawBody, array $headers): bool;
}
