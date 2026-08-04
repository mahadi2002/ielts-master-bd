# AI Build Playbook — IELTS Master BD

Feed this to your AI coding agent (Claude Code, Cursor, etc.) alongside the other 3 files. Use the session preamble at the start of **every** session to prevent drift.

---

## Session Preamble (paste at the start of every new AI session)

```
You are building "IELTS Master BD," a PHP web app defined in 4 spec files:
01-BUILD-SPEC.md, 02-SCHEMA.sql, 03-ENV-AND-CONFIG.md, 04-AI-BUILD-PLAYBOOK.md.

Hard constraints — do not deviate without asking:
- PHP 8.2+, zero Composer dependencies, hand-rolled MVC (no framework)
- Plain PHP views, one app.css, vanilla ES6 JS, no bundler
- MySQL only — no Redis, no external cache assumption
- Follow the file manifest in 01-BUILD-SPEC.md §3 exactly — do not invent new files or rename existing ones
- Follow the route table in §4 exactly
- Use the exact Bangla copy in §7 verbatim — do not paraphrase or "improve" it
- SubscriptionGateway must stay behind the interface — build against MockGateway,
  never hardcode a gateway call directly in a controller
- Every state-changing POST route needs CSRF + the middleware listed in the route table
- Re-read the relevant section of 01-BUILD-SPEC.md before generating each file
```

---

## Build Order

1. **Foundation** — `bootstrap.php`, `config.php`, `Core/Router.php`, `Core/Request.php`, `Core/Response.php`, `Core/Db.php`, `public/index.php`, `.htaccess`. *Acceptance: server responds 200 on `/` with a placeholder view.*
2. **Database** — run `02-SCHEMA.sql`, verify all FKs create cleanly, run seed rows. *Acceptance: `SHOW TABLES` matches manifest; one seed word visible via manual query.*
3. **Sessions & CSRF** — `Core/Session.php` (DB-backed), `Core/Csrf.php`, `Middleware/CsrfMiddleware.php`. *Acceptance: session persists across requests; a form without a valid token is rejected.*
4. **Auth flow (mock gateway)** — `AuthController`, `Services/MockGateway.php`, `otp_requests` handling, `otp.js`. *Acceptance: mobile entry → OTP → verify → session created → subscription row inserted via MockGateway → redirect to `/dashboard`.*
5. **Subscription middleware** — `SubscriptionMiddleware.php`, expiry handling. *Acceptance: gated route blocks an unsubscribed user; redirects with the correct flash message from §8.*
6. **Landing page** — `HomeController`, `home/landing.php`, `app.css` base. Insert exact copy from §7. *Acceptance: visual match to the copy blocks, button positions per brief (top-right button, mid-page CTA, bottom subscribe box, footer).*
7. **Dictionary search (free, rate-limited)** — `DictionaryController`, `DictionaryService`, `RateLimitMiddleware`. *Acceptance: 11th lookup in a day is blocked with the §8 banner.*
8. **Learning session + goal system** — `LearnController`, `GoalService`, `daily_progress` writes. *Acceptance: marking words increments progress; hitting target flips `goal_completed`.*
9. **Exclusive word reward + streak** — `ExclusiveWordService`, `StreakService`, `user_collection`. *Acceptance: goal completion unlocks exactly one new exclusive word, increments streak correctly per the increment/freeze/reset rules in §5.*
10. **Spaced repetition review** — `ReviewController`, `SpacedRepetitionService` (SM-2), `review/queue.php`. *Acceptance: a word answered "easy" gets a longer `interval_days`; "hard" resets it.*
11. **Quiz** — `QuizController`, `quiz.js`. *Acceptance: correct answers count toward daily goal per §5.2.*
12. **Dashboard & Collection** — `DashboardController`, `CollectionController`. *Acceptance: shows real counts from `user_word_progress` and `user_collection`, not placeholders.*
13. **Admin** — word CRUD, exclusive-pool monitor (§5.5 alert), user list. *Acceptance: admin-only routes 403 for role='user'.*
14. **Cron jobs** — `daily-reset.php`, `subscription-check.php`, `run-jobs.php`. *Acceptance: manual run resets a test user's `daily_progress` at simulated midnight without touching their streak incorrectly.*
15. **Security pass** — walk the checklist in `03-ENV-AND-CONFIG.md` line by line.

Do not start step *N+1* until step *N*'s acceptance criterion is verifiably true — not "looks done."

---

## Anti-Drift Checklist (run before accepting any AI-generated file)

- [ ] File path matches the manifest in `01-BUILD-SPEC.md` §3 exactly
- [ ] No new Composer/npm dependency introduced
- [ ] No Tailwind CDN, no framework import
- [ ] Bangla UI strings match §7 verbatim where specified
- [ ] Any new route added to `routes.php` also appears in this session's diff of §4 (flag if the AI invents an undocumented route)
- [ ] Gateway calls go through `SubscriptionGateway` interface, not inline HTTP calls in controllers
- [ ] All DB writes use prepared statements (PDO) — no string-concatenated SQL
- [ ] Escaped output (`e()`) used in every view for user-supplied or DB-sourced text

## Known Blockers (do not let the AI silently invent these)

- BDApps SDP/OTP real API contract — `BdAppsGateway.php` stays a stub until you supply it
- BTRC operator prefix list — currently assumed 016/019 (Robi), 017 (Airtel); verify before launch
- Landing page persuasion paragraph and product description — not yet written, flagged in `01-BUILD-SPEC.md` §7
- Seed word content (definitions, Bangla translations, band tagging) — needs a real source, not AI-hallucinated definitions for a paid product
