# Routes

The full table lives in `app/routes.php` — this is a reading guide to it, not
a duplicate. Middleware keys: `csrf` `guest` `auth` `sub` `admin` `rl:<bucket>`.

## Public

Anyone can hit these, no session required. `/dictionary` and `/guides` are
the free-tier SEO surface — real, useful content, not clickbait, with the
full detail gated behind a subscription.

| Route | What it does |
|---|---|
| `GET /` | Landing page — word of the day, sample quiz, subscribe box |
| `GET /dictionary`, `/dictionary/{slug}` | Free word search, capped at 10 lookups/day per IP |
| `GET /guides`, `/guides/{slug}` | Guide library — title + excerpt only |
| `GET /privacy`, `/terms`, `/about` | Static pages |
| `GET /contact`, `POST /contact` | Support form → `contact_messages`, not just an audit-log entry |
| `GET /sitemap.xml` | Generated from published words + guides |
| `GET /health` | Runs `SELECT 1` — what an uptime monitor hits |

## Auth (OTP)

| Route | What it does |
|---|---|
| `GET /subscribe` | Phone number entry |
| `POST /subscribe/otp` | Sends the code, rate-limited by IP and by the number itself |
| `GET/POST /subscribe/verify` | Code entry + verification |
| `POST /subscribe/resend` | Resend, subject to the 60s cooldown |
| `GET /login` | Same mechanism, framed for a returning subscriber |
| `POST /logout` | Ends the session |

## Gated app (`auth` + `sub` — re-checked on every request)

| Route | What it does |
|---|---|
| `GET /app` | Dashboard — streak, today's goal, weekly activity, weak words |
| `GET /app/learn`, `POST /app/learn/mark` | The daily flashcard session |
| `GET /app/words`, `/app/words/{slug}` | Full catalog browse + detail |
| `GET /app/review`, `POST /app/review/answer` | SM-2 spaced-repetition queue |
| `GET /app/quiz`, `POST /app/quiz/submit` | MCQ / fill-in-the-blank practice |
| `GET /app/collection` | Exclusive words unlocked so far |
| `GET /app/guides`, `/app/guides/{slug}` | Full guide body |
| `GET /app/calendar` | Monthly view of completed-goal days |
| `GET /app/qa`, `/app/qa/ask`, `POST /app/qa`, `/app/qa/{id}` | Ask-a-question loop |

## Account (`auth` only — no `sub`, deliberately)

Reachable regardless of subscription state, including `pending` — someone
who hasn't been charged yet still needs a way out.

| Route | What it does |
|---|---|
| `GET /account` | Status, current period, resubscribe/unsubscribe links |
| `GET/POST /account/unsubscribe` | Ends the subscription, keeps the account |
| `POST /account/delete` | Anonymizes the account (see SECURITY.md) |
| `GET /expired` | Shown when `sub` middleware blocks a gated route |

## Webhooks

| Route | What it does |
|---|---|
| `POST /webhooks/bdapps` | No CSRF (the caller is BDApps, not a browser) — authenticity comes from signature verification inside the gateway. Queues a job, never applies the state change inline. |

## Admin (`admin` — a `users.role = 'admin'` account, no separate login; optional IP allowlist)

Signs in through the same `/subscribe` OTP flow as any subscriber — the
`admin` middleware only adds a role check on top of the ordinary logged-in
state. Signs out through the same `POST /logout` too.

| Route | What it does |
|---|---|
| `GET /admin` | Subscriber counts, charge totals, exclusive-pool alerts |
| `/admin/words/*` | Word CRUD |
| `/admin/guides/*` | Guide CRUD |
| `/admin/qa/*` | Answer open questions |
| `/admin/users/*` | Search by last 4 digits, change status |
| `/admin/contact/*` | The support inbox — new / read / resolved |
| `GET /admin/logs` | Audit log viewer |
