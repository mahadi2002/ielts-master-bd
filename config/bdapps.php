<?php
declare(strict_types=1);

return [
    'driver'     => env('BDAPPS_DRIVER', 'mock'),
    'base_url'   => rtrim((string) env('BDAPPS_BASE_URL', ''), '/'),
    'app_id'     => env('BDAPPS_APP_ID'),
    'app_key'    => env('BDAPPS_APP_KEY'),
    'app_secret' => env('BDAPPS_APP_SECRET'),
    'password'   => env('BDAPPS_PASSWORD'),
    'product_id' => env('BDAPPS_PRODUCT_ID'),
    'amount'     => (float) env('BDAPPS_DAILY_AMOUNT', 2.78),
    'currency'   => env('BDAPPS_CURRENCY', 'BDT'),
    'timeout'    => (int) env('BDAPPS_TIMEOUT', 15),
    'webhook'    => [
        'secret' => env('BDAPPS_WEBHOOK_SECRET'),
        'ips'    => array_values(array_filter(array_map('trim', explode(',', (string) env('BDAPPS_WEBHOOK_IPS'))))),
    ],
    'sms' => [
        'shortcode' => env('BDAPPS_SHORTCODE', '16216'),
        'stop_word' => env('BDAPPS_STOP_KEYWORD', 'STOP'),
    ],
    'grace_hours'  => (int) env('SUB_GRACE_HOURS', 48),
    'period_hours' => (int) env('SUB_PERIOD_HOURS', 24),
];
