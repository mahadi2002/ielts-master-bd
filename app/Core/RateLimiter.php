<?php

declare(strict_types=1);

namespace App\Core;

/**
 * MySQL-backed sliding-window-ish (fixed window) rate limiter. No Redis assumption.
 */
final class RateLimiter
{
    public static function attempt(string $bucketKey, int $maxHits, int $windowSeconds): bool
    {
        $pdo = Db::pdo();
        $now = time();

        $stmt = $pdo->prepare('SELECT hits, window_started_at FROM rate_limits WHERE bucket_key = :key');
        $stmt->execute(['key' => $bucketKey]);
        $row = $stmt->fetch();

        if (!$row) {
            $ins = $pdo->prepare(
                'INSERT INTO rate_limits (bucket_key, hits, window_started_at) VALUES (:key, 1, :now)'
            );
            $ins->execute(['key' => $bucketKey, 'now' => date('Y-m-d H:i:s', $now)]);
            return true;
        }

        $windowStarted = strtotime($row['window_started_at']);
        if ($now - $windowStarted >= $windowSeconds) {
            $upd = $pdo->prepare(
                'UPDATE rate_limits SET hits = 1, window_started_at = :now WHERE bucket_key = :key'
            );
            $upd->execute(['now' => date('Y-m-d H:i:s', $now), 'key' => $bucketKey]);
            return true;
        }

        if ((int) $row['hits'] >= $maxHits) {
            return false;
        }

        $upd = $pdo->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE bucket_key = :key');
        $upd->execute(['key' => $bucketKey]);
        return true;
    }

    public static function remaining(string $bucketKey, int $maxHits, int $windowSeconds): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT hits, window_started_at FROM rate_limits WHERE bucket_key = :key');
        $stmt->execute(['key' => $bucketKey]);
        $row = $stmt->fetch();

        if (!$row) {
            return $maxHits;
        }
        if (time() - strtotime($row['window_started_at']) >= $windowSeconds) {
            return $maxHits;
        }
        return max(0, $maxHits - (int) $row['hits']);
    }
}
