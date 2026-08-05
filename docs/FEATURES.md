# Features

## Free tier (no subscription needed)

- **Dictionary search** — full word definitions, capped at 10 lookups/day
  per IP. This is the SEO surface; it needs to actually be useful on its
  own, not a teaser for the teaser.
- **Guide library index + excerpts** — real, useful summaries. The full
  guide body is the only thing gated.
- **Word of the day + a sample quiz question** on the landing page.

## Gated (subscription required)

- **Daily learning session** (`/app/learn`) — a flashcard stack sized to
  whatever's left of today's goal (`users.daily_goal_count`, default 5).
  Flip to see the definition, mark "শিখেছি" to log it and advance the goal.
- **Spaced repetition review** (`/app/review`) — SM-2 scheduling. A word
  rated "easy" gets a longer interval; "again"/"hard" resets it to daily
  review. See `SpacedRepetitionService`.
- **Quiz** (`/app/quiz`) — MCQ and fill-in-the-blank, drawn from words
  already in progress. A correct answer also counts toward the daily goal.
- **Exclusive word reward** — completing the daily goal unlocks one
  `is_exclusive` word, weighted toward the user's `target_band`, with a
  fallback to the next band down if that band's pool is empty. See
  `ExclusiveWordService`.
- **Streak** — increments on same-day-or-next-day completion; a single
  missed day is covered automatically if a freeze is available (one
  refills per week); anything past that resets to 1.
- **Word catalog browse** (`/app/words`) — the full, non-exclusive
  catalog, filterable by band.
- **Guide library, full body** (`/app/guides`).
- **Study calendar** (`/app/calendar`) — a month grid, completed-goal days
  lit up. The reason to open the app on a day you don't otherwise need
  anything reviewed.
- **Ask-a-question** (`/app/qa`) — submit a question about a word or IELTS
  in general; an admin/editor answers it; answered questions are visible to
  everyone in the app.
- **Dashboard** — streak, today's ring, weekly activity bar chart, weak
  words (low ease-factor), quiz accuracy.

## Support

- **Contact form → admin inbox**, not just an audit-log entry. New
  messages get a best-effort email heads-up (`SUPPORT_EMAIL` in `.env`) but
  the admin inbox at `/admin/contact` is the actual source of truth —
  states are `new / read / resolved`.

## Admin

- Word / guide CRUD.
- Answer open questions.
- User search by last 4 digits (never the full number), status changes.
- Subscription/charge dashboard, exclusive-pool health, audit log viewer.

## Known open items

- **Real BDApps API contract** — `BdAppsGateway` is a stub. Every method
  throws rather than guessing at an endpoint shape from documentation that
  doesn't exist yet. `MockGateway` covers local development and demoing the
  full product; production is blocked on the vendor actually providing the
  contract.
- **Seed word list** — the ~40 words in `database/seeds/content.php` are a
  small, author-written starter set for development. They need to be
  replaced with a properly licensed/curated list before launch — not
  hallucinated definitions on a product people pay for.
- **BTRC operator prefix map** — `config/operators.php` reflects the
  post-Robi-Airtel-merger allocation as understood at build time; worth a
  final check against BTRC's current published list before launch.
