<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// ---- Minimal .env loader (no Composer) ----
$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            $end = strpos($value, $quote, 1);
            $value = $end !== false ? substr($value, 1, $end - 1) : trim($value, $quote);
        } elseif (($hashPos = strpos($value, '#')) !== false) {
            $value = rtrim(substr($value, 0, $hashPos));
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$config = require BASE_PATH . '/config.php';

// ---- Error handling ----
error_reporting(E_ALL);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

set_exception_handler(function (Throwable $e) use ($config) {
    error_log('[Uncaught] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, (string) $e . "\n");
        exit(1);
    }
    http_response_code(500);
    if ($config['app']['debug']) {
        echo '<pre style="padding:2rem;font-family:monospace;white-space:pre-wrap;">';
        echo htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    } else {
        echo 'একটি সমস্যা হয়েছে। কিছুক্ষণ পর আবার চেষ্টা করুন।';
    }
});

// ---- Autoload (PSR-4-ish, zero Composer) ----
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require_once BASE_PATH . '/app/helpers.php';

// ---- Timezone ----
date_default_timezone_set('Asia/Dhaka');

// ---- Database ----
App\Core\Db::connect($config);

// ---- Session (DB-backed) + views: HTTP requests only, not cron/CLI ----
if (PHP_SAPI !== 'cli') {
    App\Core\Session::start($config);
    App\Core\View::boot($config);
    App\Core\Csrf::boot($config);
}

return $config;
