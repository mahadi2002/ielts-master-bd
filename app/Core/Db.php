<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Db
{
    private static ?PDO $instance = null;

    public static function connect(array $config): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['db']['host'],
            $config['db']['port'],
            $config['db']['database'],
            $config['db']['charset']
        );

        try {
            self::$instance = new PDO($dsn, $config['db']['username'], $config['db']['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            throw $e;
        }

        return self::$instance;
    }

    public static function pdo(): PDO
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Db::connect() must be called before Db::pdo().');
        }
        return self::$instance;
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
