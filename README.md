# IELTS Master BD — শব্দ সোপান

Daily-goal IELTS vocabulary app for Bangladeshi learners: spaced-repetition
review, band-tagged word lists, quizzes, a guide library, a study calendar,
and an ask-a-question loop — funded by a ৳2.78/day carrier-billed
subscription (Robi/Airtel), no card, no wallet app, just an OTP.

## Stack

PHP 8.2+, zero Composer dependencies, hand-rolled MVC (Router, Middleware
pipeline, Controllers, Services, Repositories, plain-PHP Views). MySQL 8 /
MariaDB with `utf8mb4`, DB-backed sessions. No JS framework — vanilla,
deferred, no build step. No ORM — a thin PDO wrapper.

## Docs

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — request lifecycle, layers, what to know before touching Sessions/Crypto/the gateway
- [`docs/ROUTES.md`](docs/ROUTES.md) — the full route table, grouped by access level
- [`docs/DATABASE.md`](docs/DATABASE.md) — every table, migration by migration
- [`docs/FEATURES.md`](docs/FEATURES.md) — what's built, what's gated, what's still open
- [`docs/SECURITY.md`](docs/SECURITY.md) — encryption, sessions, rate limiting, CSP, audit log
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — shared-cPanel deploy steps, cron entries
- [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) — local setup, the mock OTP flow, running tests

## Quick start

```bash
cp .env.example .env
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"   # run twice: APP_KEY, HASH_PEPPER
php database/migrate.php --fresh --seed
php -S 127.0.0.1:8000 -t public public/router-dev.php
```

Full walkthrough, including the mock OTP flow and demo accounts, is in
[`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md).

## Known open items

Tracked plainly rather than glossed over — see the end of
[`docs/FEATURES.md`](docs/FEATURES.md#known-open-items): the real BDApps API
contract hasn't arrived yet, the seed word list is a small dev placeholder,
and the operator prefix map is worth a final check against BTRC before
launch.
