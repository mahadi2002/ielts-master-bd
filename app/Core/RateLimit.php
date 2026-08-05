<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Fixed-window rate limiting on the `rate_limits` table.
 * MySQL-backed on purpose — no Redis assumption on shared hosting.
 */
final class RateLimit
{
    /**
     * Bucket definitions: [limit, window seconds, block seconds after breach].
     * Several buckets have two windows; both are checked.
     */
    public const BUCKETS = [
        'otp_request' => [['limit' => 3,  'window' => 3600,  'block' => 3600],
                          ['limit' => 8,  'window' => 86400, 'block' => 86400]],
        'otp_verify'  => [['limit' => 5,  'window' => 900,   'block' => 86400]],
        'otp_resend'  => [['limit' => 3,  'window' => 3600,  'block' => 3600]],
        'admin_login' => [['limit' => 5,  'window' => 900,   'block' => 900]],
        'qa_post'     => [['limit' => 5,  'window' => 86400, 'block' => 86400]],
        // Free-tier dictionary lookups: 10/day per IP, matches the product's
        // published free-tier cap — not a bot-abuse throttle, a pricing wall.
        'dict_search' => [['limit' => 10, 'window' => 86400, 'block' => 86400]],
        'contact'     => [['limit' => 3,  'window' => 3600,  'block' => 3600]],
    ];

    /**
     * Record one hit against every window of a bucket.
     *
     * @return int|null seconds until the caller may retry, or null when allowed
     */
    public static function hit(string $bucket, string $key): ?int
    {
        $windows = self::BUCKETS[$bucket] ?? [['limit' => 60, 'window' => 60, 'block' => 60]];
        $retry   = null;

        foreach ($windows as $i => $w) {
            $name = $bucket . ':' . $i . ':' . substr($key, 0, 100);
            $wait = self::hitWindow($name, (int) $w['limit'], (int) $w['window'], (int) $w['block']);
            if ($wait !== null) {
                $retry = max($retry ?? 0, $wait);
            }
        }

        return $retry;
    }

    /** Check without consuming a hit. */
    public static function tooMany(string $bucket, string $key): ?int
    {
        $windows = self::BUCKETS[$bucket] ?? [];
        $retry   = null;

        foreach ($windows as $i => $w) {
            $name = $bucket . ':' . $i . ':' . substr($key, 0, 100);
            $row  = Db::first('SELECT hits, window_start, blocked_until FROM rate_limits WHERE bucket = ?', [$name]);
            if ($row === null) {
                continue;
            }
            if ($row['blocked_until'] !== null && strtotime((string) $row['blocked_until']) > time()) {
                $retry = max($retry ?? 0, strtotime((string) $row['blocked_until']) - time());
                continue;
            }
            $elapsed = time() - strtotime((string) $row['window_start']);
            if ($elapsed < (int) $w['window'] && (int) $row['hits'] >= (int) $w['limit']) {
                $retry = max($retry ?? 0, (int) $w['window'] - $elapsed);
            }
        }

        return $retry;
    }

    public static function reset(string $bucket, string $key): void
    {
        Db::exec(
            'DELETE FROM rate_limits WHERE bucket LIKE ?',
            [$bucket . ':%:' . substr($key, 0, 100)]
        );
    }

    /** Hits consumed so far in the current window of a bucket's first window. */
    public static function remaining(string $bucket, string $key): int
    {
        $windows = self::BUCKETS[$bucket] ?? [];
        if ($windows === []) {
            return 0;
        }
        $w    = $windows[0];
        $name = $bucket . ':0:' . substr($key, 0, 100);
        $row  = Db::first('SELECT hits, window_start FROM rate_limits WHERE bucket = ?', [$name]);

        if ($row === null) {
            return (int) $w['limit'];
        }
        if (time() - strtotime((string) $row['window_start']) >= (int) $w['window']) {
            return (int) $w['limit'];
        }
        return max(0, (int) $w['limit'] - (int) $row['hits']);
    }

    private static function hitWindow(string $name, int $limit, int $window, int $block): ?int
    {
        $now = time();
        $row = Db::first('SELECT hits, window_start, blocked_until FROM rate_limits WHERE bucket = ?', [$name]);

        if ($row === null) {
            Db::exec(
                'INSERT INTO rate_limits (bucket, hits, window_start) VALUES (?, 1, NOW())
                 ON DUPLICATE KEY UPDATE hits = hits + 1',
                [$name]
            );
            return null;
        }

        if ($row['blocked_until'] !== null) {
            $until = strtotime((string) $row['blocked_until']);
            if ($until > $now) {
                return $until - $now;
            }
            Db::exec('UPDATE rate_limits SET hits = 1, window_start = NOW(), blocked_until = NULL WHERE bucket = ?', [$name]);
            return null;
        }

        $elapsed = $now - strtotime((string) $row['window_start']);

        if ($elapsed >= $window) {
            Db::exec('UPDATE rate_limits SET hits = 1, window_start = NOW() WHERE bucket = ?', [$name]);
            return null;
        }

        $hits = (int) $row['hits'] + 1;

        if ($hits > $limit) {
            Db::exec(
                'UPDATE rate_limits SET hits = ?, blocked_until = DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE bucket = ?',
                [$hits, $block, $name]
            );
            return $block;
        }

        Db::exec('UPDATE rate_limits SET hits = ? WHERE bucket = ?', [$hits, $name]);
        return null;
    }

    /** "৫৯ মিনিট" / "৪৫ সেকেন্ড" — turns a raw second count into the wording shown on rate-limit screens. */
    public static function humanWait(int $seconds): string
    {
        if ($seconds < 60) {
            return bn_num(max(1, $seconds)) . ' সেকেন্ড';
        }
        if ($seconds < 3600) {
            return bn_num((int) ceil($seconds / 60)) . ' মিনিট';
        }
        return bn_num((int) ceil($seconds / 3600)) . ' ঘণ্টা';
    }
}
