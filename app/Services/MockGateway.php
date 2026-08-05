<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Crypto;
use App\Core\Logger;
use App\Core\Session;
use App\Exceptions\OtpException;
use App\Support\Operators;

/**
 * Development gateway — lets the whole product be built and tested without
 * needing real BDApps credentials.
 *
 *   • sendOtp() generates a real code, writes it to storage/logs/otp-*.log and
 *     (when APP_DEBUG=true) flashes it on screen. It returns the code in the
 *     `code` key, which tells OtpService to verify locally.
 *   • The universal dev code 123456 always verifies.
 *   • charge() fails "soft" (insufficient balance → grace path) when the MSISDN
 *     ends in 00, and "hard" (→ expiry) when it ends in 99.
 */
final class MockGateway implements SubscriptionGateway
{
    public const DEV_CODE = '123456';

    private const SIM_FILE = '/storage/cache/mock-subscribers.json';

    public function sendOtp(string $msisdn): array
    {
        $length = (int) config('app.otp.length', 6);
        $ttl    = (int) config('app.otp.ttl', 300);

        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
        $ref  = 'mock_' . Crypto::randomToken(8);

        // Dev only. The production driver never reaches this code path, and
        // bootstrap.php refuses to boot with BDAPPS_DRIVER=mock in production.
        Logger::channel('otp', sprintf('ref=%s to=***%s code=%s', $ref, Operators::last4($msisdn), $code));

        if (config('app.debug')) {
            Session::flash('_dev_otp', $code);
        }

        return ['ref' => $ref, 'expires_at' => time() + $ttl, 'code' => $code];
    }

    public function verifyOtp(string $ref, string $code, string $msisdn): array
    {
        if ($code === '') {
            throw new OtpException('invalid', 'Code মিলছে না।');
        }

        $subscriberRef = 'mocksub_' . substr(Crypto::blindIndex($msisdn), 0, 24);
        $this->rememberSuffix($subscriberRef, $msisdn);

        return ['subscriber_ref' => $subscriberRef];
    }

    public function charge(string $subscriberRef, string $idempotencyKey, float $amount): array
    {
        return match ($this->suffixFor($subscriberRef)) {
            '00' => [
                'status' => 'failed', 'txn_id' => null, 'code' => 'INSUFFICIENT_BALANCE',
                'raw' => ['mock' => true, 'reason' => 'insufficient balance'],
            ],
            '99' => [
                'status' => 'failed', 'txn_id' => null, 'code' => 'HARD_FAIL',
                'raw' => ['mock' => true, 'reason' => 'subscriber barred'],
            ],
            default => [
                'status' => 'success',
                'txn_id' => 'mocktxn_' . substr(sha1($idempotencyKey), 0, 16),
                'code'   => 'OK',
                'raw'    => ['mock' => true, 'amount' => $amount],
            ],
        };
    }

    public function status(string $subscriberRef): array
    {
        return ['status' => 'active', 'raw' => ['mock' => true]];
    }

    public function unsubscribe(string $subscriberRef): bool
    {
        Logger::info('mock.unsubscribe', ['ref' => substr($subscriberRef, 0, 12) . '…']);
        return true;
    }

    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $secret = (string) config('bdapps.webhook.secret', '');
        if ($secret === '') {
            return true; // dev: accept anything so the webhook flow can be exercised
        }
        return hash_equals(
            hash_hmac('sha256', $rawBody, $secret),
            (string) ($headers['x-signature'] ?? '')
        );
    }

    // ── charge simulation bookkeeping ───────────────────────────────────
    //
    // The cron runs in a separate process from the OTP verify, so the mapping
    // subscriber_ref → last-two-digits is persisted to a small JSON file
    // rather than kept in memory.

    private function rememberSuffix(string $subscriberRef, string $msisdn): void
    {
        $data = $this->simData();
        $data[$subscriberRef] = substr($msisdn, -2);
        @file_put_contents(APP_ROOT . self::SIM_FILE, json_encode($data), LOCK_EX);
    }

    private function suffixFor(string $subscriberRef): string
    {
        return (string) ($this->simData()[$subscriberRef] ?? '');
    }

    /** @return array<string,string> */
    private function simData(): array
    {
        $file = APP_ROOT . self::SIM_FILE;
        if (!is_file($file)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($file), true);
        return is_array($decoded) ? array_map('strval', $decoded) : [];
    }
}
