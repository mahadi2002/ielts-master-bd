<?php
declare(strict_types=1);

/**
 * Seeds one admin account and one plain demo subscriber so the app can be
 * exercised immediately after a fresh install, without waiting on a real
 * BDApps OTP round-trip. Both sign in exactly the same way — /subscribe,
 * OTP, done — the admin account differs only by its `role` column.
 *
 * Change both numbers (or at minimum re-run through /account/delete for
 * the admin one) before any real deployment.
 */

use App\Core\Crypto;
use App\Core\Db;

function seedSubscriber(string $msisdn, string $operator, string $role): void
{
    $hash   = Crypto::blindIndex($msisdn);
    $userId = Db::value('SELECT id FROM users WHERE msisdn_hash = ?', [$hash]);

    if ($userId !== null) {
        return;
    }

    $userId = Db::insert(
        'INSERT INTO users (msisdn_hash, msisdn_enc, msisdn_last4, operator, target_band, role, status, created_at)
         VALUES (?, ?, ?, ?, 7.0, ?, "active", NOW())',
        [$hash, Crypto::encrypt($msisdn), substr($msisdn, -4), $operator, $role]
    );

    Db::insert(
        'INSERT INTO subscriptions (user_id, bdapps_sub_ref, status, daily_amount, started_at, current_period_end)
         VALUES (?, ?, "active", 2.78, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))',
        [$userId, 'demo_' . Crypto::randomToken(8)]
    );

    Db::insert(
        'INSERT INTO streaks (user_id, current_streak, longest_streak, freezes_available, freezes_reset_at)
         VALUES (?, 0, 0, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY))',
        [$userId]
    );

    out('  ' . ucfirst($role) . ' account: ' . $msisdn . ' (dev OTP 123456, or the code shown on screen in APP_DEBUG mode)');
}

// Robi (018) — admin. Airtel (016) — a plain subscriber. Different prefixes
// so the two are easy to tell apart while testing.
seedSubscriber('01811000000', 'robi', 'admin');
seedSubscriber('01611000000', 'airtel', 'user');
