# TODO

## Content
- 43 words seeded (31 free-tier + 12 exclusive-reward), across bands 6-9.
  That's a development-scale starter set, not a launch-ready catalog —
  wants to be a few hundred words minimum, sourced from a properly licensed
  list rather than more author-written entries.
- 6 guides, one per category (writing_task1, writing_task2, speaking,
  listening, reading, vocabulary). Each category needs several more before
  the guide library reads as a real library instead of a sampler.
- Someone who actually speaks Bangla natively needs to read through all the
  word/guide copy before this goes live. I wrote it, it should be fine, but
  "should be fine" isn't good enough for something people are paying for.
- Quiz coverage is one MCQ + one fill-in-the-blank per word, generated
  mechanically from the synonym list and example sentence. Fine as a
  starting point; hand-written distractors would make the MCQs harder to
  guess without knowing the word.

## Billing
- `BdAppsGateway` is stubbed — every method throws, on purpose, because
  there's no real API doc yet to build against. Everything else (OTP, the
  state machine, webhooks, the charge cron) works end to end against
  `MockGateway`. Swapping to production is filling in the real endpoint
  shapes once BDApps provides them, plus flipping `BDAPPS_DRIVER` in `.env`.
- `config/operators.php`'s Robi/Airtel prefix mapping (018 Robi, 016 Airtel
  post-merger) reflects the current BTRC allocation as best understood —
  worth a direct check with BDApps before launch, since this determines who
  can even sign up.

## Before actually shipping this
- Fresh `APP_KEY` / `HASH_PEPPER` on the real server, not the dev ones
  committed nowhere but generated locally.
- Delete the seeded admin account and demo subscriber (`01611000000`)
  before going live — or at minimum change the admin password immediately.
- Get real BDApps credentials, flip `BDAPPS_DRIVER` to `bdapps`, test one
  real charge before opening signups.
- Nightly `mysqldump` + actually test a restore once, not just assume it
  works.
- Run a proper security scan against a staging copy.
- Actually deploy it somewhere (see `docs/DEPLOYMENT.md`) and point an
  uptime monitor at `/health` — right now this only exists on a local dev
  server, so it has zero real uptime by definition, not because of a code
  problem.
- Set `SUPPORT_EMAIL` in `.env` once there's a real inbox to send the
  heads-up to. Without it, contact-form submissions still land in
  `/admin/contact` just fine — the email is a nice-to-have, not the only
  way to see them.

## Nice to have, not urgent
- FULLTEXT search on `words` only works on real MySQL — MariaDB doesn't
  ship the ngram parser, so it silently falls back to a `LIKE` prefix
  match. Fine at this catalog size; would want real fulltext if the word
  list grows into the thousands.
- Admin TOTP 2FA — the `admins.totp_secret` column exists, nothing reads
  it yet.
- SMS review reminders — needs its own BDApps SMS quota and costs money
  per message; punting until the daily-charge margin can absorb it.
- The band-weighted exclusive-word selection (`ExclusiveWordService`)
  falls back one band down when a band's pool is empty, but there's no
  admin UI yet to bulk-add exclusive words for a specific band in response
  to the low-pool alert — right now that's a manual `/admin/words` entry
  at a time.
