# Deployment

Target is shared cPanel hosting — nothing here assumes root, Redis, a queue
daemon, or more than one PHP-FPM worker process. It upgrades cleanly to a VPS
later with no application code changes, just more of the same primitives.

## Directory layout on the server

```
/home/USER/ieltsmasterbd/        ← app code (NOT web-accessible)
/home/USER/public_html/          ← docroot → ieltsmasterbd/public
```

If `public/` can't be symlinked as the docroot, copy its *contents* into
`public_html/` and set `APP_ROOT` explicitly at the top of `public/index.php`
(the comment there shows the exact line).

## First deploy

1. Upload everything except `.env` (never commit or upload real secrets).
2. Create the database and an app user with no DDL rights:
   ```sql
   CREATE DATABASE ieltsmasterbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'imbd_app'@'localhost' IDENTIFIED BY 'CHANGE_ME';
   GRANT SELECT, INSERT, UPDATE, DELETE ON ieltsmasterbd.* TO 'imbd_app'@'localhost';
   ```
   Migrations run under a separate, DDL-capable credential
   (`DB_MIGRATE_USER`/`DB_MIGRATE_PASS`) so an application-level SQL bug can
   never `DROP TABLE`.
3. Copy `.env.example` to `.env`, fill it in. Generate the two secrets:
   ```
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```
   Run that twice, once for `APP_KEY`, once for `HASH_PEPPER`. They must be
   different values.
4. `php database/migrate.php --seed`
5. Point the docroot at `public/`, confirm `public/.htaccess` is being read
   (shared hosts occasionally disable `AllowOverride` — ask support if
   rewrites 404 instead of routing).
6. One crontab line drives everything:
   ```
   * * * * * php /home/USER/ieltsmasterbd/cron/queue_worker.php >> /home/USER/ieltsmasterbd/storage/logs/cron.log 2>&1
   0 * * * *  php /home/USER/ieltsmasterbd/cron/charge_cycle.php >> /home/USER/ieltsmasterbd/storage/logs/cron.log 2>&1
   15 0 * * * php /home/USER/ieltsmasterbd/cron/study_tasks.php  >> /home/USER/ieltsmasterbd/storage/logs/cron.log 2>&1
   0 3 * * *  php /home/USER/ieltsmasterbd/cron/cleanup.php      >> /home/USER/ieltsmasterbd/storage/logs/cron.log 2>&1
   ```
7. Point an external uptime monitor at `GET /health` — it runs a real
   `SELECT 1`, not just a static 200.

## Before flipping `BDAPPS_DRIVER=bdapps`

- `bootstrap.php` refuses to boot with `BDAPPS_DRIVER=mock` when
  `APP_ENV=production` — this is enforced, not just documented.
- `BdAppsGateway` is currently a stub (every method throws). It needs the
  real API contract from BDApps before this flag can be flipped at all —
  see FEATURES.md's open items.

## What would need to change to run on more than one server

Everything stateful already lives in MySQL — sessions, rate limits, the
job queue, cached mock-gateway state (`storage/cache/mock-subscribers.json`,
dev-only). The one thing that doesn't generalize past a single box: cron
itself. `cron/_lock.php` uses a local `flock()`, which only prevents two
overlapping runs *on the same machine* — running the same crontab on two
app servers would need those four lines moved to a single dedicated worker
box, or the lock swapped for a DB-row-based lock (`GET_LOCK()`in MySQL is
the least-effort version of that). Nothing else here assumes local disk.

## Log files

`storage/logs/app-YYYY-MM-DD.log` (structured, rotates daily),
`storage/logs/otp-YYYY-MM-DD.log` (dev only — `MockGateway` writes generated
codes here, never in production). Neither ever contains a full phone number,
an OTP code, or `APP_KEY`/`HASH_PEPPER` — see SECURITY.md.
