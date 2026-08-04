<?php

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'IELTS Master BD',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'url' => rtrim($_ENV['APP_URL'] ?? '', '/'),
        'key' => $_ENV['APP_KEY'] ?? '',
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME_MINUTES'] ?? 120),
        'secure' => filter_var($_ENV['SESSION_COOKIE_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],
    'subscription' => [
        'gateway' => $_ENV['SUBSCRIPTION_GATEWAY'] ?? 'mock',
        'bdapps' => [
            'base' => $_ENV['BDAPPS_API_BASE'] ?? '',
            'key' => $_ENV['BDAPPS_API_KEY'] ?? '',
            'secret' => $_ENV['BDAPPS_API_SECRET'] ?? '',
            'callback_secret' => $_ENV['BDAPPS_CALLBACK_SECRET'] ?? '',
            'service_id' => $_ENV['BDAPPS_SERVICE_ID'] ?? '',
        ],
        'daily_amount' => (float)($_ENV['BDAPPS_DAILY_AMOUNT'] ?? 2.78),
    ],
    'otp' => [
        'length' => (int)($_ENV['OTP_LENGTH'] ?? 6),
        'ttl' => (int)($_ENV['OTP_TTL_SECONDS'] ?? 300),
        'resend_cooldown' => (int)($_ENV['OTP_RESEND_COOLDOWN_SECONDS'] ?? 60),
        'max_attempts' => (int)($_ENV['OTP_MAX_ATTEMPTS'] ?? 3),
    ],
    'rate_limits' => [
        'dict_free_per_day' => (int)($_ENV['DICT_FREE_LOOKUPS_PER_DAY'] ?? 10),
        'otp_per_hour' => (int)($_ENV['RATE_LIMIT_OTP_PER_HOUR'] ?? 5),
    ],
    'log' => [
        'channel' => $_ENV['LOG_CHANNEL'] ?? 'file',
        'level' => $_ENV['LOG_LEVEL'] ?? 'error',
    ],
];
