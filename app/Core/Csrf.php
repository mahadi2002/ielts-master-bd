<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Double-submit-cookie CSRF, not session-stored: sessions.user_id is NOT NULL
 * in the schema (server-side revocation is keyed to authenticated users only),
 * so DbSessionHandler never persists anonymous sessions — a session-stored
 * token would silently fail CSRF on every logged-out form (e.g. /subscribe).
 */
final class Csrf
{
    private const COOKIE_NAME = 'imbd_csrf';
    private static bool $secure = false;

    public static function boot(array $config): void
    {
        self::$secure = (bool) $config['session']['secure'];
    }

    public static function token(): string
    {
        if (!empty($_COOKIE[self::COOKIE_NAME])) {
            return $_COOKIE[self::COOKIE_NAME];
        }

        $token = bin2hex(random_bytes(32));
        setcookie(self::COOKIE_NAME, $token, [
            'expires' => time() + 86400,
            'path' => '/',
            'httponly' => false,
            'secure' => self::$secure,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE_NAME] = $token;

        return $token;
    }

    public static function verify(?string $token): bool
    {
        $cookie = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (empty($cookie) || empty($token)) {
            return false;
        }
        return hash_equals($cookie, $token);
    }
}
