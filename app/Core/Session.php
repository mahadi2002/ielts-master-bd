<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use SessionHandlerInterface;

final class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $pdo;
    private int $lifetimeSeconds;

    public function __construct(PDO $pdo, int $lifetimeMinutes)
    {
        $this->pdo = $pdo;
        $this->lifetimeSeconds = $lifetimeMinutes * 60;
    }

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
        $stmt = $this->pdo->prepare(
            'SELECT payload FROM sessions WHERE id = :id AND last_activity >= :cutoff'
        );
        $stmt->execute([
            'id' => $id,
            'cutoff' => date('Y-m-d H:i:s', time() - $this->lifetimeSeconds),
        ]);
        $row = $stmt->fetch();
        return $row ? (string) $row['payload'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            // sessions.user_id is NOT NULL — skip persisting anonymous sessions.
            return true;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
             VALUES (:id, :user_id, :ip, :ua, :payload, :now)
             ON DUPLICATE KEY UPDATE user_id = :user_id2, ip_address = :ip2, user_agent = :ua2,
                payload = :payload2, last_activity = :now2'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'payload' => $data,
            'now' => date('Y-m-d H:i:s'),
            'user_id2' => $userId,
            'ip2' => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua2' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'payload2' => $data,
            'now2' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < :cutoff');
        $stmt->execute(['cutoff' => date('Y-m-d H:i:s', time() - $max_lifetime)]);
        return $stmt->rowCount();
    }
}

final class Session
{
    private static ?array $userCache = null;

    public static function start(array $config): void
    {
        // Server can't revoke a session that only lives in a cookie-signed payload;
        // subscription expiry must be enforceable mid-session, so DB-backed storage is required.
        $handler = new DbSessionHandler(Db::pdo(), $config['session']['lifetime']);
        session_set_save_handler($handler, true);

        session_name('imbd_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'secure' => $config['session']['secure'],
            'samesite' => 'Lax',
        ]);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function login(string $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::$userCache = null;
    }

    public static function userId(): ?string
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function user(): ?array
    {
        $userId = self::userId();
        if ($userId === null) {
            return null;
        }
        if (self::$userCache !== null && self::$userCache['id'] === $userId) {
            return self::$userCache;
        }
        $stmt = Db::pdo()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch() ?: null;
        self::$userCache = $user;
        return $user;
    }

    public static function flashSet(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function flash(string $key): mixed
    {
        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }
        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function setOld(array $input): void
    {
        $_SESSION['_old'] = $input;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
