<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * AES-256-GCM for MSISDN at rest, plus a keyed blind index for lookups —
 * the ciphertext needs APP_KEY to reverse, the index needs HASH_PEPPER to
 * forge, and neither secret alone is enough to do anything useful with it.
 *
 * APP_KEY and HASH_PEPPER live in .env only. Rotating HASH_PEPPER invalidates
 * every blind index in the database — it is a migration, not a config change.
 */
final class Crypto
{
    private const CIPHER  = 'aes-256-gcm';
    private const IV_LEN  = 12;
    private const TAG_LEN = 16;

    private static function key(): string
    {
        $key = (string) config('app.key', '');
        if (strlen($key) !== 32) {
            throw new RuntimeException('APP_KEY must decode to exactly 32 bytes.');
        }
        return $key;
    }

    private static function pepper(): string
    {
        $pepper = (string) config('app.pepper', '');
        if (strlen($pepper) !== 32) {
            throw new RuntimeException('HASH_PEPPER must decode to exactly 32 bytes.');
        }
        return $pepper;
    }

    /** @return string base64(iv|tag|ciphertext) */
    public static function encrypt(string $plaintext): string
    {
        $iv  = random_bytes(self::IV_LEN);
        $tag = '';

        $ct = openssl_encrypt($plaintext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($ct === false) {
            throw new RuntimeException('Encryption failed.');
        }

        return base64_encode($iv . $tag . $ct);
    }

    public static function decrypt(string $blob): ?string
    {
        $raw = base64_decode($blob, true);
        if ($raw === false || strlen($raw) <= self::IV_LEN + self::TAG_LEN) {
            return null;
        }

        $iv  = substr($raw, 0, self::IV_LEN);
        $tag = substr($raw, self::IV_LEN, self::TAG_LEN);
        $ct  = substr($raw, self::IV_LEN + self::TAG_LEN);

        $plain = openssl_decrypt($ct, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        return $plain === false ? null : $plain;
    }

    /** Deterministic, keyed lookup key. Never reversible. */
    public static function blindIndex(string $value): string
    {
        return hash_hmac('sha256', $value, self::pepper());
    }

    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
