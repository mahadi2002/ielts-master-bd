# Environment & Configuration Reference

## .env.example

```
APP_NAME="IELTS Master BD"
APP_ENV=local              # local | production
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=                   # 32-byte random, generate via: php -r "echo bin2hex(random_bytes(32));"

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ieltsmasterbd
DB_USERNAME=ieltsmasterbd_app
DB_PASSWORD=

SESSION_LIFETIME_MINUTES=120
SESSION_COOKIE_SECURE=false   # true in production (HTTPS)

# Subscription gateway
SUBSCRIPTION_GATEWAY=mock      # mock | bdapps
BDAPPS_API_BASE=               # set when contract received
BDAPPS_API_KEY=
BDAPPS_API_SECRET=
BDAPPS_CALLBACK_SECRET=        # verifies inbound callback signature
BDAPPS_SERVICE_ID=
BDAPPS_DAILY_AMOUNT=2.78

# OTP
OTP_LENGTH=6
OTP_TTL_SECONDS=300
OTP_RESEND_COOLDOWN_SECONDS=60
OTP_MAX_ATTEMPTS=3

# Rate limits
DICT_FREE_LOOKUPS_PER_DAY=10
RATE_LIMIT_OTP_PER_HOUR=5

# Mail (streak reminders — optional at MVP)
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@ieltsmasterbd.example

LOG_CHANNEL=file
LOG_LEVEL=debug            # debug (local) | error (production)
```

## config.php (loaded by bootstrap.php)

```php
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
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
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
];
```

## public/.htaccess

```apacheconf
RewriteEngine On

# Force HTTPS in production (comment out for local dev)
RewriteCond %{HTTPS} off
RewriteCond %{HTTP_HOST} !^localhost
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
    Header set Content-Security-Policy "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; frame-ancestors 'none'"
    Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Deny access to sensitive paths from the web
<FilesMatch "\.(env|sql|log|md)$">
    Require all denied
</FilesMatch>
```

## MySQL privilege setup

```sql
CREATE DATABASE ieltsmasterbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ieltsmasterbd_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON ieltsmasterbd.* TO 'ieltsmasterbd_app'@'localhost';
-- No DDL/DROP grants for the app user — migrations run under a separate admin credential.
FLUSH PRIVILEGES;
```

## Cron entry

```
* * * * * php /home/USER/ieltsmasterbd/cron/run-jobs.php >> /home/USER/ieltsmasterbd/storage/logs/cron.log 2>&1
```
`run-jobs.php` polls the `jobs` table and dispatches `daily-reset.php` / `subscription-check.php` logic — keeps a single cron line regardless of how many scheduled tasks you add later.

## Security checklist (must hold before launch)

- [ ] `APP_DEBUG=false` and `LOG_LEVEL=error` in production
- [ ] All user input passed through `Validator` before DB write
- [ ] All output passed through `e()` in views (XSS)
- [ ] All state-changing POST routes require CSRF token
- [ ] Passwords/OTP never logged; OTP stored as hash, not plaintext
- [ ] Mobile number validated server-side against Robi/Airtel prefixes (016/019, 017) — do not trust client-side JS validation alone
- [ ] Session cookies: `HttpOnly`, `Secure` (prod), `SameSite=Lax`
- [ ] Rate limits enforced server-side on OTP send, OTP verify, dictionary search
- [ ] Admin routes behind separate role check, not just "logged in"
- [ ] DB app user has no DDL/DROP grants (see privilege setup above)
- [ ] BDApps callback endpoint verifies signature (`BDAPPS_CALLBACK_SECRET`) before trusting payload
