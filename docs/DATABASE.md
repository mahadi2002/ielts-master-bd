# Database

MySQL 8 / MariaDB 10.4+, `utf8mb4_unicode_ci` throughout. Migrations are
plain numbered `.sql` files in `database/migrations/`, applied in order by
`database/migrate.php`. There's no migration framework — each file is just
`CREATE TABLE IF NOT EXISTS`, run once and tracked in a `migrations` table.

## 001_identity.sql — users, billing, OTP

- **users** — `msisdn_hash` (HMAC blind index, the actual lookup key) and
  `msisdn_enc` (AES-256-GCM ciphertext) are separate columns for a reason —
  see SECURITY.md. `msisdn_last4` is the only part of the number ever
  rendered in a template. `role` is `'user'` or `'admin'` — there is no
  separate staff table; an admin is a subscriber whose account has this
  flag set, and signs in through the identical `/subscribe` OTP flow.
  Everyone gets in the same way.
- **subscriptions** — one row per subscription attempt, never deleted or
  reused across a cancel-and-return cycle. `status` is the state machine:
  `pending → active ⇄ grace → expired`, or `→ unsubscribed` from anywhere.
- **charge_transactions** — the billing ledger. `idempotency_key` is
  `sha1(subscription_id:YYYY-MM-DD)` with a UNIQUE constraint — that's what
  actually stops a double charge if the hourly cron and a first-charge
  attempt on signup race each other, not application logic.
- **otp_requests** — `otp_hash` is `password_hash()`, never plaintext.

## 002_content.sql — the catalog

- **words** — the vocabulary catalog. `is_exclusive = 1` marks the
  reward-only pool that `ExclusiveWordService` draws from; those rows are
  never returned by the public dictionary search or the gated catalog
  browse, only unlocked one at a time by completing a daily goal.
- **quizzes** — MCQ / fill-in-the-blank questions, one word can have several.
- **guides** — `excerpt` is the free teaser (always safe to `SELECT *`),
  `body_md` is the gated column. See ARCHITECTURE.md for how the query split
  keeps that column out of the public code path entirely.

## 003_user_data.sql — progress, streaks, the UGC loop

- **user_word_progress** — one row per (user, word), the SM-2 state:
  `ease_factor`, `interval_days`, `repetitions`, `next_review_date`.
- **daily_progress** — one row per (user, date), created lazily on first
  activity of the day. `goal_completed` flips once, `exclusive_word_id`
  records what was unlocked.
- **streaks** — `freezes_available` refills weekly; `study_tasks.php`
  (cron) breaks a streak that's gone more than one day past its grace.
- **user_collection** — the unlocked exclusive words, one row per unlock.
- **quiz_attempts** — every attempt, correct or not, for accuracy stats.
- **qa_questions** — the ask-a-question loop. `answered_by` is the
  `users.id` of whichever `role = 'admin'` account answered, but the UI
  never shows who specifically — answers are attributed to "the team,"
  not a phone number. This is moderated expert Q&A, not an open forum.

## 004_ops.sql — sessions, security, queue

- **sessions** — DB-backed, see ARCHITECTURE.md for why.
- **rate_limits** — fixed-window counters, one row per bucket+key+window.
- **audit_log** — append-only. `meta` is JSON but the app never puts a full
  MSISDN or OTP code in it, even by accident — see `AuditService::scrub()`.
- **webhook_events** — every inbound BDApps callback, keyed by the
  provider's own idempotency id so a retried webhook is a no-op.
- **jobs** — a minimal queue table. Currently the only job type is
  `webhook.apply`; `queue_worker.php` drains it every minute.
- **admin_alerts** — raised by `ExclusiveWordService` when a band's reward
  pool drops under ~14 unclaimed words, so someone notices before it runs
  dry mid-reward.

## 005_support.sql — contact inbox

- **contact_messages** — `status` is `new → read → resolved`. This is the
  actual support channel; the audit log records that a message came in, but
  this table is what the admin UI reads from.
