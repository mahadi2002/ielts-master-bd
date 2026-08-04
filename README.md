# IELTS Master BD — শব্দ সোপান

Daily-goal IELTS vocabulary app for Bangladeshi learners: spaced-repetition review,
band-tagged word lists, quizzes, streaks, and an exclusive-word reward loop.
Subscription is BDApps SDP-style (Robi/Airtel), ৳2.78/day.

## Tech stack

- **Backend:** PHP 8.2+, hand-rolled MVC, zero Composer dependencies
- **Frontend:** Plain PHP views, one hand-written stylesheet (`public/assets/css/app.css`), vanilla ES6 (no bundler)
- **Database:** MySQL 8 / MariaDB 10.4+, `utf8mb4_unicode_ci`
- **Sessions / rate limiting / queue:** MySQL tables — no Redis assumption
- **Payments:** `SubscriptionGateway` interface → `MockGateway` (default/dev) or `BdAppsGateway` (stub, pending BDApps API contract)

See `docs/` (the original 4 spec files) for the full build spec, schema, and env reference.

## Local setup

1. **PHP & MySQL** — any PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl` (XAMPP/WAMP/Laragon all work).

2. **Create the database:**
   ```sql
   CREATE DATABASE ieltsmasterbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'ieltsmasterbd_app'@'localhost' IDENTIFIED BY 'change-me';
   GRANT SELECT, INSERT, UPDATE, DELETE ON ieltsmasterbd.* TO 'ieltsmasterbd_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Run the schema and seed data** (in order):
   ```bash
   mysql -u root ieltsmasterbd < database/migrations/001_initial_schema.sql
   mysql -u root ieltsmasterbd < database/seed/001_words_and_quizzes.sql
   mysql -u root ieltsmasterbd < database/seed/002_admin_user.sql
   ```

4. **Configure environment:**
   ```bash
   cp .env.example .env
   php -r "echo bin2hex(random_bytes(32));"   # paste into APP_KEY
   ```
   Fill in `DB_*` to match step 2. Leave `SUBSCRIPTION_GATEWAY=mock` — no real
   BDApps credentials are needed for local development.

5. **Run it:**
   ```bash
   php -S localhost:8000 -t public
   ```
   Visit `http://localhost:8000`.

6. **Admin access:** subscribe through the normal `/subscribe` OTP flow using
   mobile number `01700000000` (seeded with `role = 'admin'`) — with
   `APP_DEBUG=true` the OTP is echoed back in the flash message / JSON response
   so you don't need a real SMS gateway. Then visit `/admin/words`.

7. **Cron** (optional locally): `php cron/run-jobs.php` — drives the daily
   streak-break check and the subscription renewal poll. In production, add
   the one-line crontab entry from `docs/03-ENV-AND-CONFIG.md`.

## Known blockers (do not silently invent these)

- Real BDApps SDP/OTP API contract — `app/Services/BdAppsGateway.php` stays a stub until it arrives.
- BTRC operator prefixes — currently 016/019 (Robi), 017 (Airtel); verify before launch.
- Seed word list (`database/seed/001_words_and_quizzes.sql`) is a small author-written starter set for
  development — replace with a properly licensed/curated list before launch.

## Project layout

```
public/          web root (front controller, assets)
app/             Core/, Middleware/, Controllers/, Models/, Services/, Views/
database/        migrations + seed SQL
cron/            daily-reset.php, subscription-check.php, run-jobs.php (single crontab entry)
storage/         logs, cache
```
