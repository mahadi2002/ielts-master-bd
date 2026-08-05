# Development

## Setup

1. Any LAMP-shaped local stack works — XAMPP is what this was built against.
2. Create the database and run migrations + seeds:
   ```
   php database/migrate.php --fresh --seed
   ```
   `--fresh` drops and recreates the schema — refused outright when
   `APP_ENV=production`, so it's safe to reach for locally without thinking
   twice.
3. Copy `.env.example` to `.env`, generate `APP_KEY` and `HASH_PEPPER`:
   ```
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```
   Leave `BDAPPS_DRIVER=mock` — the whole product is buildable and
   demoable against it, no real billing credentials needed.
4. Run it:
   ```
   php -S 127.0.0.1:8000 -t public public/router-dev.php
   ```
   `router-dev.php` exists only so the built-in server serves static assets
   under `public/assets/` the way Apache/`.htaccess` does in production —
   without it every asset request would fall through to the front
   controller and 404.

## Trying the OTP flow without a real phone

`MockGateway` generates a real 6-digit code and, when `APP_DEBUG=true`,
flashes it directly on the OTP screen — no SMS needed. The universal dev
code `123456` also always verifies. Charge simulation is deterministic by
phone number suffix: a number ending in `00` fails soft (→ grace), `99`
fails hard (→ stays unpaid), anything else succeeds.

The seed data includes two accounts, both already active so `/login` can be
exercised immediately: a plain subscriber (`01611000000`, Airtel) and an
admin (`01811000000`, Robi, `role = 'admin'`) — same OTP sign-in for both,
the admin one just also gets `/admin`.

## Running cron manually

Each script refuses to run outside `PHP_SAPI === 'cli'` and takes its own
`flock()`, so it's safe to just run them directly while testing:

```
php cron/queue_worker.php
php cron/charge_cycle.php
php cron/study_tasks.php
php cron/cleanup.php
```

## Before calling a change done

- `php -l` every touched file.
- `php tests/smoke.php` — a handful of plain assertions against the real
  security primitives (CSRF token round-trip, MSISDN normalization,
  blind-index determinism, SM-2 scheduling math), not mocked collaborators.
- Actually drive the feature in a browser — fill in the form, click the
  button, read what comes back. Passing lint and a clean review aren't the
  same claim as "this was watched working."
- `grep -rn "§\|spec §\|per the plan"` — a sweep for leftover citations from
  planning docs that shouldn't appear in code comments or in `docs/`.

## Code style

Comments explain *why*, never *what* — a well-named function already says
what it does. Nothing should read like it's quoting an internal spec or
narrating an automated build step; write it the way you'd explain the
decision to a teammate who's looking at this file a year from now with no
other context.
