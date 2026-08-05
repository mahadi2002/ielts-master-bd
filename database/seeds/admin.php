<?php
declare(strict_types=1);

/**
 * Seeds one admin/staff login and one demo subscriber so the app can be
 * exercised immediately after a fresh install, without waiting on a real
 * BDApps OTP round-trip.
 *
 * Change both sets of credentials before any real deployment.
 */

use App\Core\Crypto;
use App\Core\Db;

$adminEmail = 'admin@ieltsmasterbd.example';
$exists = Db::value('SELECT id FROM admins WHERE email = ?', [$adminEmail]);

if ($exists === null) {
    Db::insert(
        'INSERT INTO admins (email, password_hash, name, role, is_active, created_at)
         VALUES (?, ?, ?, "admin", 1, NOW())',
        [$adminEmail, password_hash('ChangeMe!2026', PASSWORD_ARGON2ID), 'Admin']
    );
    out('  Admin login: ' . $adminEmail . ' / ChangeMe!2026 (change immediately)');
}

// A demo subscriber (01611000000, Airtel prefix) with an active subscription,
// so /login can be exercised without going through OTP first.
$demoMsisdn = '01611000000';
$demoHash   = Crypto::blindIndex($demoMsisdn);
$userId     = Db::value('SELECT id FROM users WHERE msisdn_hash = ?', [$demoHash]);

if ($userId === null) {
    $userId = Db::insert(
        'INSERT INTO users (msisdn_hash, msisdn_enc, msisdn_last4, operator, target_band, status, created_at)
         VALUES (?, ?, ?, "airtel", 7.0, "active", NOW())',
        [$demoHash, Crypto::encrypt($demoMsisdn), substr($demoMsisdn, -4)]
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

    out('  Demo subscriber: ' . $demoMsisdn . ' (dev OTP 123456, or use the mock-flow debug code shown on screen)');
}
