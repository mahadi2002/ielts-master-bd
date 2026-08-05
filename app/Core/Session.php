<?php
declare(strict_types=1);

namespace App\Core;

use SessionHandlerInterface;

/**
 * DB-backed session handler.
 *
 * Storing sessions in MySQL is what makes server-side revocation possible:
 * when a subscription lapses, SubscriptionService deletes the user's rows
 * and an already-open browser loses access on its very next request.
 *
 * Bound to ua_hash only — mobile IPs rotate constantly on this network, so
 * IP binding would log real users out every few minutes.
 */
final class Session implements SessionHandlerInterface
{
    private static bool $started = false;
    private static ?array $flashRead = null;

    public static function start(Request $request): void
    {
        if (self::$started || PHP_SAPI === 'cli') {
            return;
        }

        $cfg = config('app.session');

        session_set_save_handler(new self(), true);
        session_name((string) $cfg['cookie']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (bool) $cfg['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) ($cfg['lifetime_min'] * 60));

        session_start();
        self::$started = true;

        $now = time();
        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = $now;
            $_SESSION['_ua']         = $request->uaHash();
        } elseif (
            $now - (int) $_SESSION['_created_at'] > $cfg['absolute_days'] * 86400
            || (isset($_SESSION['_ua']) && !hash_equals((string) $_SESSION['_ua'], $request->uaHash()))
        ) {
            self::destroy_all();
            session_start();
            $_SESSION['_created_at'] = $now;
            $_SESSION['_ua']         = $request->uaHash();
        }

        // Read-and-clear the previous request's flash bag exactly once.
        self::$flashRead = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
    }

    // ── Public API ───────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function userId(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        return $id === null ? null : (int) $id;
    }

    public static function regenerate(): void
    {
        if (self::$started) {
            session_regenerate_id(true);
        }
    }

    /** Queue a value for the *next* request only. */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function flashGet(string $key, mixed $default = null): mixed
    {
        return self::$flashRead[$key] ?? $default;
    }

    /** ['type' => 'success'|'error'|'info'|'warn', 'text' => '…'] */
    public static function notify(string $type, string $text): void
    {
        self::flash('_notice', ['type' => $type, 'text' => $text]);
    }

    public static function notice(): ?array
    {
        $n = self::flashGet('_notice');
        return is_array($n) ? $n : null;
    }

    public static function destroy_all(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        self::$started = false;
    }

    /** Server-side revocation — used by SubscriptionService and account delete. */
    public static function revokeAllForUser(int $userId): void
    {
        Db::exec('DELETE FROM sessions WHERE user_id = ?', [$userId]);
    }

    // ── SessionHandlerInterface ──────────────────────────────────────────

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $row = Db::first('SELECT payload FROM sessions WHERE id = ?', [$id]);
        return $row === null ? '' : (string) $row['payload'];
    }

    public function write(string $id, string $data): bool
    {
        $now    = time();
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

        Db::exec(
            'INSERT INTO sessions (id, user_id, ip_hash, ua_hash, payload, last_activity, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                payload = VALUES(payload),
                last_activity = VALUES(last_activity)',
            [
                $id,
                $userId,
                $_SESSION['_ip'] ?? null,
                $_SESSION['_ua'] ?? null,
                $data,
                $now,
                $now,
            ]
        );

        return true;
    }

    public function destroy(string $id): bool
    {
        Db::exec('DELETE FROM sessions WHERE id = ?', [$id]);
        return true;
    }

    public function gc(int $maxLifetime): int|false
    {
        return Db::exec('DELETE FROM sessions WHERE last_activity < ?', [time() - $maxLifetime]);
    }
}
