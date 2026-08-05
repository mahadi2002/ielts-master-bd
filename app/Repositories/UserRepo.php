<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Crypto;
use App\Core\Db;
use App\Support\Operators;

final class UserRepo
{
    private const COLUMNS = 'id, msisdn_hash, msisdn_last4, operator, display_name, target_band,
                             daily_goal_type, daily_goal_count, role, status, locale, created_at,
                             last_login_at, anonymized_at';

    public function find(int $id): ?array
    {
        return Db::first('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?', [$id]);
    }

    public function findByMsisdn(string $msisdn): ?array
    {
        return Db::first(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE msisdn_hash = ?',
            [Crypto::blindIndex((string) Operators::normalize($msisdn))]
        );
    }

    /**
     * Create on first subscribe, or return the existing row for a returning
     * number. The users row is never duplicated and never deleted — billing
     * history lives in the subscriptions table, keyed off this one row.
     */
    public function findOrCreate(string $msisdn): array
    {
        $normalized = (string) Operators::normalize($msisdn);
        $existing   = $this->findByMsisdn($normalized);

        if ($existing !== null) {
            return $existing;
        }

        $id = Db::insert(
            'INSERT INTO users (msisdn_hash, msisdn_enc, msisdn_last4, operator, status)
             VALUES (?, ?, ?, ?, "pending")',
            [
                Crypto::blindIndex($normalized),
                Crypto::encrypt($normalized),
                Operators::last4($normalized),
                (string) Operators::detect($normalized),
            ]
        );

        return (array) $this->find($id);
    }

    /** Decrypt on demand — only the charging cron and support tooling need this. */
    public function msisdnFor(int $userId): ?string
    {
        $enc = Db::value('SELECT msisdn_enc FROM users WHERE id = ?', [$userId]);
        return $enc === null ? null : Crypto::decrypt((string) $enc);
    }

    public function touchLogin(int $userId): void
    {
        Db::exec('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$userId]);
    }

    public function updateProfile(int $userId, ?string $displayName, ?float $targetBand): void
    {
        Db::exec(
            'UPDATE users SET display_name = ?, target_band = ? WHERE id = ?',
            [$displayName ?: null, $targetBand, $userId]
        );
    }

    public function setGoal(int $userId, string $goalType, int $goalCount): void
    {
        Db::exec(
            'UPDATE users SET daily_goal_type = ?, daily_goal_count = ? WHERE id = ?',
            [$goalType, max(1, $goalCount), $userId]
        );
    }

    public function setStatus(int $userId, string $status): void
    {
        Db::exec('UPDATE users SET status = ? WHERE id = ?', [$status, $userId]);
    }

    /**
     * Irreversibly strip the identifiers while keeping the row, so foreign
     * keys and learning/billing history stay intact — this is how "delete my
     * account" actually gets implemented without breaking every table that
     * references it.
     */
    public function anonymize(int $userId): void
    {
        Db::exec(
            'UPDATE users
                SET msisdn_hash = SHA2(CONCAT("anon:", id, ":", UUID()), 256),
                    msisdn_enc = "",
                    msisdn_last4 = "0000",
                    display_name = NULL,
                    status = "blocked",
                    anonymized_at = NOW()
              WHERE id = ? AND anonymized_at IS NULL',
            [$userId]
        );
    }

    /** Admin search — by last 4 digits only. Full numbers are never displayed. */
    public function searchByLast4(string $last4, int $limit = 50): array
    {
        return Db::all(
            'SELECT u.id, u.msisdn_last4, u.operator, u.status, u.created_at, u.last_login_at,
                    s.status AS sub_status, s.current_period_end
               FROM users u
               LEFT JOIN subscriptions s ON s.id = (
                    SELECT id FROM subscriptions WHERE user_id = u.id ORDER BY id DESC LIMIT 1
               )
              WHERE u.msisdn_last4 = ?
              ORDER BY u.id DESC
              LIMIT ' . (int) $limit,
            [$last4]
        );
    }

    public function recent(int $limit = 50): array
    {
        return Db::all(
            'SELECT u.id, u.msisdn_last4, u.operator, u.status, u.created_at, u.last_login_at,
                    s.status AS sub_status, s.current_period_end
               FROM users u
               LEFT JOIN subscriptions s ON s.id = (
                    SELECT id FROM subscriptions WHERE user_id = u.id ORDER BY id DESC LIMIT 1
               )
              ORDER BY u.id DESC
              LIMIT ' . (int) $limit
        );
    }

    public function countActive(): int
    {
        return (int) Db::value(
            'SELECT COUNT(DISTINCT user_id) FROM subscriptions
              WHERE status IN ("active","grace") AND current_period_end > NOW()'
        );
    }

    public function countNewSince(int $days): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$days]
        );
    }
}
