# Architecture

No framework here, which means no magic either — every request goes through
the same handful of files and you can trace it start to finish without
jumping into a vendor folder.

## Request lifecycle

Every request hits `public/index.php`. That's the only PHP file the web
server ever executes directly. It:

1. Loads `app/bootstrap.php` — sets up the autoloader, reads `.env`, checks
   a few things that would be bad to get wrong in production (debug mode on,
   mock billing driver on, missing encryption keys), registers error handlers.
2. Builds a `Request` from the superglobals.
3. Starts the session (custom DB-backed handler, more on that below).
4. Hands the request to the `Router`, which matches it against the table in
   `app/routes.php` and runs whatever middleware that route lists, then
   calls the controller action.
5. Wraps the whole thing in a try/catch — an `HttpException` becomes the
   matching error page (404, 419, 429...), anything else becomes a generic
   500 with the real error only shown when `APP_DEBUG=true`.
6. Security headers get applied to the response right before it goes out,
   regardless of what happened above — even error pages get CSP/X-Frame-Options/etc.

There's no dependency injection container. Controllers `new` up whatever
Repository or Service they need directly. For an app this size that's
simpler to read than wiring everything through a container.

## Layers

```
Controllers/    thin — pull input, call a service or repo, pick a view
Controllers/Admin/  separate admin auth, CRUD for every content type, the
                contact inbox, the audit-log viewer
Services/       the actual business logic (billing state machine, OTP
                flow, spaced repetition scheduling, exclusive-word
                selection, calendar aggregation)
Repositories/   all the SQL lives here, nowhere else. Every query is a
                prepared statement. Sort columns are read from an allowlist
                in config/content.php, never taken from user input directly.
Core/           the framework-shaped stuff: Router, Request, Response,
                View, Db, Session, Csrf, Crypto, Validator, RateLimit,
                Logger, Markdown, Helpers.php (global functions)
Middleware/     one class per cross-cutting concern, chained per-route
```

Views are plain PHP with one enforced rule: anything printed goes through
`e()`. There's a tiny layout system (`View::render()` looks for
`$this->layout('layouts/whatever', [...])` at the top of a template and
wraps the rendered output in it) but no templating language, no compiler —
it's just PHP requiring PHP files with `extract()`'d variables.

## Things worth knowing before you touch them

**Sessions live in the database**, not in files. The whole reason for that:
when someone's subscription lapses, we need to kill their session *right
now*, not whenever PHP's garbage collector gets around to expiring a file.
`SubscriptionService` deletes the relevant `sessions` rows the moment access
is lost, so the next request from that browser — even mid-page — gets
bounced. See `App\Core\Session` and `App\Services\SubscriptionService`.

**Nothing decides access from the session.** `RequireSubscription` (the
middleware gating everything under `/app`) re-reads the subscription status
from the database on every single request. No cached flag, no "logged in =
paid" shortcut. Slightly more DB load, way harder to get wrong.

**Phone numbers are encrypted, not hashed-for-lookup-only.** `users.msisdn_enc`
is AES-256-GCM ciphertext (needs `APP_KEY` to reverse), and a separate
one-way HMAC (`users.msisdn_hash`, keyed by `HASH_PEPPER`) is what queries
actually filter on. Two different secrets, two different jobs — losing
`HASH_PEPPER` alone breaks lookups but doesn't expose anything; losing
`APP_KEY` alone doesn't let you find a specific user's row without also
having the hash. See `App\Core\Crypto`.

**The billing gateway is behind an interface.** `SubscriptionGateway` has
two implementations — `MockGateway` for local dev (generates real OTPs,
simulates charge success/failure based on the phone number's last two
digits) and `BdAppsGateway` for production, which nobody outside this one
class knows exists. It's currently a stub: the real BDApps API contract
hasn't arrived yet, so every method throws rather than guessing at an
endpoint shape. Swap the driver via `.env`, nothing else moves.

**Free and gated content are separate queries, not separate views of the
same query.** `GuideRepo::findTeaserBySlug()` never selects `body_md`;
`GuideRepo::findBySlug()` (full body) is only ever called from a route
behind `RequireSubscription`. The same split applies to exclusive reward
words — `WordRepo::findPublicBySlug()` filters `is_exclusive = 0`, and the
gated word page checks the user actually unlocked an exclusive word before
showing it. This is enforced at the query, not the template — a bug in a
view can't leak what was never fetched.

## Cron jobs

Four scripts in `cron/`, each wrapped in a file lock so two overlapping runs
don't double-process anything:

- `charge_cycle.php` — hourly, charges whatever subscriptions are due
- `queue_worker.php` — every minute, drains the `jobs` table (right now
  that's just applying webhook events after they've already been
  acknowledged, so BDApps isn't kept waiting on a slow DB write)
- `study_tasks.php` — daily, breaks streaks for users who went quiet past
  their one-day freeze grace
- `cleanup.php` — daily, purges old rate-limit rows, expired OTPs, stale
  sessions, and anonymizes users who unsubscribed a long time ago
