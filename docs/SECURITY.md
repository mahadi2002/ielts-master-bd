# Security

## Phone numbers

`users.msisdn_enc` is AES-256-GCM ciphertext (`App\Core\Crypto::encrypt`),
reversible only with `APP_KEY`. `users.msisdn_hash` is a separate HMAC-SHA256
blind index, keyed by `HASH_PEPPER` — every lookup query filters on this
column, never on the encrypted one. Two different secrets doing two
different jobs: losing `HASH_PEPPER` breaks lookups but exposes nothing;
losing `APP_KEY` alone doesn't let you find a specific user's row without
also having the hash. Rotating `HASH_PEPPER` invalidates every existing
blind index — that's a migration, not a config change.

Only the last 4 digits (`msisdn_last4`) ever reach a template or an admin
screen. The full number is decrypted only by the charging cron and — if it's
ever built — support tooling that specifically needs it.

## Sessions

DB-backed (`sessions` table), not files. See ARCHITECTURE.md for why —
short version, a subscription lapsing has to kill access on the *next*
request, and only a DB row can be invalidated that fast.

Sessions are bound to a UA hash, not an IP — mobile IPs on this network
rotate constantly, and IP-binding would log real users out mid-session.

## OTP

6 digits, `random_int()`, stored as `password_hash()`, never plaintext,
never logged in production (`MockGateway` logs the dev code to
`storage/logs/otp-*.log`, and only because `BDAPPS_DRIVER=mock` is refused
in production by `bootstrap.php`). TTL 5 minutes, single use, 3 wrong
attempts kills the row and forces a fresh request, resend gated by a 60s
cooldown.

## Rate limiting

MySQL-backed fixed windows (`rate_limits` table, `App\Core\RateLimit`) — no
Redis assumption. Every meaningfully abusable action is limited on **both**
the requesting IP and the identity being acted on (the phone number for
OTP, the user id for Q&A posts) — IP-only throttling would lock out an
entire shared network over one bad actor on it.

## Bot mitigation

Honeypot field (`website`, hidden via CSS, a bot's autofill catches it) plus
a minimum-fill-time check (2 seconds from page render to submit) on every
public form that doesn't already require an OTP round-trip — subscribe,
contact. No CAPTCHA; it's a worse experience for a real user on a slow
connection for no real gain against a targeted bot.

## CSRF

Every state-changing POST route carries `csrf` middleware, no exceptions —
including "internal-only" admin forms. The one route with no CSRF check is
the BDApps webhook, because its caller isn't a browser holding our session
cookie; authenticity there comes from signature verification instead.

## Content Security Policy

`script-src 'self'; style-src 'self'`, no `unsafe-inline` anywhere. That
means:

- No inline `<script>` blocks or `onclick=""` attributes — every behavior is
  wired up from `app.js` via `addEventListener` and `data-*` attributes.
- No `style=""` attributes for a dynamic value (a progress-bar width, a
  chart-bar height). Those go through `data-bar-height` and a matching
  `element.style.property = value` line in JS — CSP governs the HTML
  attribute and inline `<style>` blocks, not a script setting a CSSOM
  property directly, so this is the CSP-safe way to do it.

## Audit log

`audit_log` — append-only, for anything that touches identity or money:
OTP sent/verified, every subscription state transition, admin logins,
contact-form submissions, account deletions. `AuditService::scrub()`
strips `msisdn`/`otp`/`code`/`password` keys from `meta` even if a caller
passes them by accident — an audit log that could leak the thing it's
auditing access to wouldn't be much of one.

## Account deletion

`UserRepo::anonymize()` replaces `msisdn_hash` with a random one-way value,
blanks `msisdn_enc`, sets `status = blocked`, and stamps `anonymized_at` —
the row itself is never deleted, because `subscriptions`, `charge_transactions`,
and `quiz_attempts` all foreign-key to it and billing history has to survive
account deletion. This is also what the daily `cleanup.php` cron applies
automatically to accounts that unsubscribed and never came back within the
retention window (`ANONYMIZE_AFTER_DAYS`).

## Never named in user-facing copy

The billing/SMS vendor's name doesn't appear anywhere a visitor reads —
not the footer, not the charge disclaimer, not Terms/Privacy. It's
abstracted behind `SubscriptionGateway`; the name lives in exactly one
class and `.env`.
