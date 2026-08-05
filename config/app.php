<?php
declare(strict_types=1);

return [
    'name'     => env('APP_NAME', 'IELTS Master BD'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false) === true || env('APP_DEBUG') === 'true',
    'url'      => rtrim((string) env('APP_URL', ''), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Dhaka'),
    'locale'   => env('APP_LOCALE', 'bn'),
    'key'      => base64_decode((string) env('APP_KEY', ''), true) ?: '',
    'pepper'   => base64_decode((string) env('HASH_PEPPER', ''), true) ?: '',

    'session'  => [
        'cookie'        => env('SESSION_COOKIE_NAME', 'imbd_sid'),
        'lifetime_min'  => (int) env('SESSION_LIFETIME_MINUTES', 1440),
        'absolute_days' => (int) env('SESSION_ABSOLUTE_DAYS', 30),
        'secure'        => env('SESSION_SECURE', 'true') !== 'false',
    ],

    'otp' => [
        'length'          => (int) env('OTP_LENGTH', 6),
        'ttl'             => (int) env('OTP_TTL_SECONDS', 300),
        'max_attempts'    => (int) env('OTP_MAX_ATTEMPTS', 3),
        'resend_cooldown' => (int) env('OTP_RESEND_COOLDOWN', 60),
    ],

    'admin_ip_allowlist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_IP_ALLOWLIST', ''))
    ))),

    'log_level'     => env('LOG_LEVEL', 'info'),
    'support_email' => env('SUPPORT_EMAIL', ''),

    'retention' => [
        'otp_hours'      => (int) env('RETAIN_OTP_HOURS', 24),
        'ratelimit_days' => (int) env('RETAIN_RATELIMIT_DAYS', 7),
        'webhook_days'   => (int) env('RETAIN_WEBHOOK_DAYS', 90),
        'session_days'   => (int) env('RETAIN_SESSION_DAYS', 30),
        'audit_days'     => (int) env('RETAIN_AUDIT_DAYS', 365),
        'anonymize_days' => (int) env('ANONYMIZE_AFTER_DAYS', 180),
    ],
];
