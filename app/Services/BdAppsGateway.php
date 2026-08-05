<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\GatewayException;

/**
 * Production driver. Stubbed: the real BDApps SDP/OTP API contract (endpoints,
 * request/response shapes, callback signature format) has not been received
 * yet — see docs/FEATURES.md for the open item. Every method throws until
 * the real integration is wired in; bootstrap.php refuses to boot this driver
 * in production, and GatewayFactory refuses it in production too, so there is
 * no path for this stub to run against real users.
 */
final class BdAppsGateway implements SubscriptionGateway
{
    public function sendOtp(string $msisdn): array
    {
        throw new GatewayException('BdAppsGateway::sendOtp is not implemented — BDApps API contract not received.');
    }

    public function verifyOtp(string $ref, string $code, string $msisdn): array
    {
        throw new GatewayException('BdAppsGateway::verifyOtp is not implemented — BDApps API contract not received.');
    }

    public function charge(string $subscriberRef, string $idempotencyKey, float $amount): array
    {
        throw new GatewayException('BdAppsGateway::charge is not implemented — BDApps API contract not received.');
    }

    public function status(string $subscriberRef): array
    {
        throw new GatewayException('BdAppsGateway::status is not implemented — BDApps API contract not received.');
    }

    public function unsubscribe(string $subscriberRef): bool
    {
        throw new GatewayException('BdAppsGateway::unsubscribe is not implemented — BDApps API contract not received.');
    }

    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $secret = (string) config('bdapps.webhook.secret', '');
        if ($secret === '') {
            return false; // production must have a real secret configured
        }
        return hash_equals(
            hash_hmac('sha256', $rawBody, $secret),
            (string) ($headers['x-signature'] ?? '')
        );
    }
}
