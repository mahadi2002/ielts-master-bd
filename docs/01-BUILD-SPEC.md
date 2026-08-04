# IELTS Master BD — Master Build Specification

Mirrors the GardenBondhu architecture pattern (plain PHP MVC, zero Composer, MockGateway) adapted to a daily-goal vocabulary product. Hosting undecided → build to the **shared-hosting baseline**; it runs unchanged on a VPS. BDApps credentials not yet available → build entirely against `MockGateway`, swap one env var at launch.

---

## 0. Locked Decisions

| Area | Decision | Rationale |
|---|---|---|
| Architecture | Single front controller, hand-rolled MVC | No framework = less AI hallucination, no Composer dependency |
| Templating | Plain PHP views + mandatory `e()` escape helper | No Twig, no build step |
| Database | MySQL 8 / MariaDB 10.4+, `utf8mb4_unicode_ci` | Bangla text correctness |
| Sessions | Custom DB session handler (`sessions` table) | Works on shared hosting; server-side revocation on subscription expiry |
| Cache / rate limit | MySQL tables (`rate_limits`) | No Redis assumption |
| Queue | `jobs` table + one cron entry | SRS scheduling, daily reset, subscription renewal checks |
| CSS | Hand-written, compiled to one `app.css`. No Tailwind CDN | CDN forces `unsafe-inline` CSP |
| JS | Vanilla ES6, no framework, no bundler | Core flows work without JS (progressive enhancement) |
| Payment gateway | `SubscriptionGateway` interface → `MockGateway` (dev) + `BdAppsGateway` (prod stub) | No BDApps docs yet — build and test the whole product against the mock |
| Language | Bangla-first UI, English for technical/IELTS terms already standard (Band, IELTS, Quiz) | Matches your source copy |
| Web root | `public/` only | |

---

## 1. Product Scope

| Domain | What breaks for a self-studying IELTS candidate | Feature |
|---|---|---|
| Vocabulary acquisition | Random word lists, no daily discipline | Daily Goal + Learning Session |
| Retention | Learn once, forget in a week | Spaced Repetition Review Queue (SM-2) |
| Motivation | No reason to return daily | Streak counter + **Exclusive Word** reward on goal completion |
| Exam relevance | Generic dictionary words aren't exam-weighted | Band-tagged word lists (6/7/8/9), Task 1/2 collocations tag |
| Active recall | Passive reading doesn't test retention | Quiz (MCQ, fill-in-blank, synonym match) |
| Discoverability | No SEO surface | Free Dictionary Search (rate-limited, ungated) |
| Self-tracking | No visibility into progress | Dashboard: words learned/week, retention %, weak-word list |
| Return habit | Miss a day, quit entirely | Streak-freeze (1 free/week), reminder email |

**Free (SEO surface, ungated):** landing page, dictionary search (capped, e.g. 10 lookups/day), sample word-of-the-day, one sample quiz.
**Paid (gated behind active subscription):** daily goal session, exclusive-word reward, streak tracking, full SRS review queue, band-tagged lists, unlimited quizzes, progress dashboard, "My Collection."

---

## 2. Tech Stack

| Layer | Choice |
|---|---|
| Backend language | PHP 8.2+ (procedural-lite hand-rolled MVC, zero Composer packages) |
| Frontend | Plain PHP views, hand-written CSS (`app.css`), vanilla ES6 JS |
| Fonts | Self-hosted `Hind Siliguri` (Bangla) + `Inter` (Latin/numerals), subsetted woff2 |
| Database | MySQL 8 / MariaDB 10.4+ |
| Sessions/cache/queue | MySQL tables (`sessions`, `rate_limits`, `jobs`) — no Redis assumption |
| Scheduling | 1 cron entry (1–5 min) driving `jobs` table |
| Payment | `SubscriptionGateway` interface: `MockGateway` (dev/test) → `BdAppsGateway` (prod, stubbed until credentials arrive) |
| Hosting | Shared cPanel baseline (PHP-FPM/CGI, `mod_rewrite`, cron, `pdo_mysql`, `mbstring`, `openssl`, `curl`). Upgrades cleanly to a VPS (nginx + PHP-FPM + Redis) with no application code changes. |

**Not assumed:** root, Redis, Composer, Supervisor, `proc_open`.

**Directory placement (cPanel):**
```
/home/USER/ieltsmasterbd/        ← app code (NOT public)
/home/USER/public_html/          ← symlink or docroot → ieltsmasterbd/public
```

---

## 3. File Manifest

```
ieltsmasterbd/
├── public/                                  ← WEB ROOT
│   ├── index.php                             front controller
│   ├── .htaccess                             rewrite + security headers + deny rules
│   ├── robots.txt
│   ├── sitemap.php                           dynamic sitemap (dictionary word pages)
│   ├── favicon.svg
│   └── assets/
│       ├── css/app.css                       the ONLY stylesheet
│       ├── js/app.js                         nav, progress ring, streak animation
│       ├── js/otp.js                         mobile input mask, OTP resend timer
│       ├── js/flashcard.js                   learning-session card flip/swipe
│       ├── js/quiz.js                        quiz state, scoring, submit
│       ├── fonts/                            self-hosted woff2
│       └── img/                              logo, badges, illustrations
│
├── app/
│   ├── bootstrap.php                         env load, error handler, session start
│   ├── routes.php                            THE route table (§4)
│   │
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php                      html(), json(), redirect()
│   │   ├── View.php                          render($template, $data) + layout
│   │   ├── Db.php                             PDO singleton + transaction helper
│   │   ├── Session.php                        DB session handler + flash
│   │   ├── Csrf.php
│   │   ├── Validator.php                      rule-based, Bangla error messages
│   │   ├── Crypto.php                          hashing, token generation
│   │   └── RateLimiter.php                     MySQL-backed, per-IP + per-user
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── SubscriptionMiddleware.php          blocks gated routes if not subscribed
│   │   ├── CsrfMiddleware.php
│   │   └── RateLimitMiddleware.php
│   │
│   ├── Controllers/
│   │   ├── HomeController.php                  landing page
│   │   ├── AuthController.php                  mobile entry → OTP → subscribe
│   │   ├── DictionaryController.php            free search (rate-limited)
│   │   ├── LearnController.php                 daily learning session
│   │   ├── ReviewController.php                 SRS review queue
│   │   ├── QuizController.php
│   │   ├── DashboardController.php             progress analytics
│   │   ├── CollectionController.php            unlocked exclusive words
│   │   └── AdminController.php                  word CRUD, exclusive pool, users
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Word.php
│   │   ├── UserWordProgress.php
│   │   ├── DailyProgress.php
│   │   ├── Streak.php
│   │   ├── UserCollection.php
│   │   ├── Quiz.php / QuizAttempt.php
│   │   └── Subscription.php
│   │
│   ├── Services/
│   │   ├── SubscriptionGateway.php             interface
│   │   ├── MockGateway.php
│   │   ├── BdAppsGateway.php                    stub, real endpoints TBD
│   │   ├── SpacedRepetitionService.php          SM-2 scheduling
│   │   ├── GoalService.php                      progress increment + completion check
│   │   ├── StreakService.php                    increment/freeze/reset logic
│   │   ├── ExclusiveWordService.php             reward selection, pool depletion guard
│   │   └── DictionaryService.php                 search, free-tier rate cap
│   │
│   └── Views/
│       ├── layout.php
│       ├── home/landing.php
│       ├── auth/mobile-entry.php, auth/otp.php
│       ├── learn/session.php
│       ├── review/queue.php
│       ├── quiz/index.php, quiz/result.php
│       ├── dashboard/index.php
│       ├── collection/index.php
│       ├── dictionary/search.php, dictionary/word.php
│       └── admin/words.php, admin/users.php, admin/exclusive-pool.php
│
├── database/
│   ├── migrations/                             see 02-SCHEMA.sql
│   └── seed/                                    starter word set, IELTS band tags
│
├── cron/
│   ├── daily-reset.php                          midnight reset, streak-break check
│   └── subscription-check.php                   renewal/charge status poll via gateway
│
├── storage/
│   ├── logs/
│   └── cache/
│
├── .env.example                                 see 03-ENV-AND-CONFIG.md
└── config.php
```

---

## 4. Route Table

| Method | Path | Controller@method | Middleware | Gated? |
|---|---|---|---|---|
| GET | `/` | Home@landing | — | No |
| GET | `/dictionary` | Dictionary@search | RateLimit | No (capped) |
| GET | `/dictionary/{word}` | Dictionary@show | RateLimit | No (capped) |
| GET | `/subscribe` | Auth@mobileEntry | — | No |
| POST | `/subscribe/otp/send` | Auth@sendOtp | Csrf, RateLimit | No |
| POST | `/subscribe/otp/verify` | Auth@verifyOtp | Csrf, RateLimit | No |
| GET | `/subscribe/callback` | Auth@gatewayCallback | — (gateway-signed) | No |
| GET | `/dashboard` | Dashboard@index | Auth, Subscription | Yes |
| GET | `/learn` | Learn@session | Auth, Subscription | Yes |
| POST | `/learn/mark` | Learn@markWord | Auth, Subscription, Csrf | Yes |
| GET | `/review` | Review@queue | Auth, Subscription | Yes |
| POST | `/review/answer` | Review@submitAnswer | Auth, Subscription, Csrf | Yes |
| GET | `/quiz` | Quiz@index | Auth, Subscription | Yes |
| POST | `/quiz/submit` | Quiz@submit | Auth, Subscription, Csrf | Yes |
| GET | `/collection` | Collection@index | Auth, Subscription | Yes |
| GET | `/logout` | Auth@logout | Auth | Yes |
| GET | `/admin/*` | Admin@* | Auth, AdminOnly | Yes (staff) |

---

## 5. Goal & Reward Logic (implementation contract)

1. `daily_progress` row created lazily on first action of the day (`user_id`, `date` unique).
2. Every "learned" mark or correct quiz answer → `GoalService::increment($userId)`.
3. On `goal_achieved >= goal_target`: set `goal_completed = true`, call `ExclusiveWordService::unlock($userId)` → selects one `words.is_exclusive = true` row not already in `user_collection`, weighted toward the user's `target_band`; insert into `user_collection`; call `StreakService::onGoalCompleted($userId)`.
4. Streak: if `last_completed_date = yesterday` → `current_streak += 1`; if gap = 1 day and `freezes_available > 0` → consume freeze, hold streak; else reset to 1.
5. **Guard:** `ExclusiveWordService` must alert admin (log + dashboard flag) when the unclaimed exclusive pool for any band drops below 14 days of runway — this reward loop breaking silently kills retention.

---

## 6. Design System — "শব্দ সোপান" (Word Steps)

| Token | Value |
|---|---|
| Primary | `#1B4D3E` (deep exam-green — trust, focus) |
| Accent / reward | `#E8A94A` (gold — exclusive word unlock, streak flame) |
| Background | `#FAF9F6` |
| Text | `#1A1A1A` / muted `#5C5C5C` |
| Error | `#C0392B` |
| Success | `#2E8B57` |
| Font (Bangla) | Hind Siliguri, 400/600/700 |
| Font (Latin/numerals) | Inter, 400/600/700 |
| Radius | 12px cards, 8px inputs/buttons |
| Signature component | Circular daily-progress ring (SVG, animates on each `learn/mark`) around the streak flame icon |

---

## 7. Exact UI Copy (verbatim — do not paraphrase)

**Top-right header button:**
```
মাত্র ৳2.78/day
```

**Mid-page CTA block:**
```
🚀 এখনই Start করুন — মাত্র ৳2.78/day
Robi & Airtel Users Only  |  যেকোনো সময় Unsubscribe করুন
```
followed by button: `Subscribe Now`

**Bottom subscription box:**
```
আপনার Robi বা Airtel Number দিন
Instant Access পাবেন সব IELTS Content-এ!

Mobile Number
01XXXXXXXXX
শুধু Robi (016/019) ও Airtel (017) Number

⚡
Daily মাত্র ৳2.78 — যেকোনো সময় Unsubscribe করুন
OTP পাঠান →
```

**Footer:**
```
Privacy Policy
Terms & Conditions
Contact Us
Powered by BDApps

Robi & Airtel Bangladesh

© 2026 IELTS Master BD — সর্বস্বত্ব সংরক্ষিত

⚠️ এই Service BDApps-এর মাধ্যমে Charge করা হয়। Daily ৳2.78 আপনার Robi /Airtel Account থেকে কাটা হবে। Unsubscribe করতে STOP লিখে 16216 নম্বরে SMS করুন।
```

**Not yet written (needs your input before build):** product description paragraph (what the site is), the "why subscribe at this rate" persuasion paragraph, mobile-number validation error strings, OTP screen copy, exclusive-word unlock celebration copy.

---

## 8. Error / Edge-Case Matrix

| Case | User-facing behavior |
|---|---|
| Wrong operator prefix (not 016/017/019) | Inline error under input, form not submitted |
| OTP timeout | "OTP মেয়াদ শেষ" + resend button (60s cooldown) |
| OTP wrong (3 attempts) | Lock resend for 5 min, rate-limit table entry |
| Subscription charge fails | Redirect to `/subscribe` with reason banner, session not created |
| Duplicate subscribe (already active) | Redirect straight to `/dashboard` |
| Subscription expires mid-session | Middleware catches on next gated request → soft redirect to `/subscribe?expired=1`, session flash explains |
| Daily goal already completed | `/learn` shows "আজকের লক্ষ্য পূর্ণ ✅" + review-queue CTA instead of new words |
| Exclusive word pool empty for user's band | Fallback to next-lower band pool, admin alert fires (see §5.5) |
| CSRF fail | 419-style page, "সেশন মেয়াদ শেষ, আবার চেষ্টা করুন" |
| Free dictionary cap hit | Inline banner: "আজকের ফ্রি লিমিট শেষ — Subscribe করুন" |

---

## 9. Open Items Before Build Starts

1. Real BDApps SDP/OTP API contract (endpoint, params, callback signature, VAT wording) — blocked until you have BDApps dashboard access.
2. BTRC operator prefix list — verify 016/019 (Robi), 017 (Airtel) is current.
3. Landing page product-description and persuasion paragraphs (Bangla, verbatim).
4. Seed word list: source (licensed dictionary API vs manual curation) and initial count per band.
5. Plant— n/a. Exclusive-word content licensing/sourcing plan.

Confirm items 3–4 and I'll write the exact seed content and remaining copy into the schema/seed files.
