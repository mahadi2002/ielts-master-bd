<?php
declare(strict_types=1);

/**
 * The route table. Format: [method, path, 'Controller@action', [middleware]].
 *
 * Middleware keys: csrf | guest | auth | sub | admin | rl:<bucket>
 * SecurityHeaders is applied globally in public/index.php, not per route.
 *
 * Order matters: literal paths must precede {slug} patterns that would also
 * match (e.g. /app/words/finder-style statics before /app/words/{slug}).
 */
return [
    // ── Public ──────────────────────────────────────────────────────────
    ['GET',  '/',                    'HomeController@index',          []],
    ['GET',  '/privacy',             'HomeController@privacy',        []],
    ['GET',  '/terms',               'HomeController@terms',          []],
    ['GET',  '/about',               'HomeController@about',          []],
    ['GET',  '/contact',             'HomeController@contact',        []],
    ['POST', '/contact',             'HomeController@submitContact',  ['csrf', 'rl:contact']],
    ['GET',  '/sitemap.xml',         'HomeController@sitemap',        []],

    ['GET',  '/dictionary',          'DictionaryController@index',    ['rl:dict_search']],
    ['GET',  '/dictionary/{slug}',   'DictionaryController@show',     ['rl:dict_search']],

    ['GET',  '/guides',              'GuideController@index',         []],
    ['GET',  '/guides/{slug}',       'GuideController@show',          []],

    // ── Auth (mobile OTP) ────────────────────────────────────────────────
    ['GET',  '/subscribe',           'AuthController@phoneForm',      ['guest']],
    ['POST', '/subscribe/otp',       'AuthController@requestOtp',     ['guest', 'csrf', 'rl:otp_request']],
    ['GET',  '/subscribe/verify',    'AuthController@otpForm',        ['guest']],
    ['POST', '/subscribe/verify',    'AuthController@verifyOtp',      ['guest', 'csrf', 'rl:otp_verify']],
    ['POST', '/subscribe/resend',    'AuthController@resendOtp',      ['guest', 'csrf', 'rl:otp_request']],
    ['GET',  '/login',               'AuthController@login',          ['guest']],
    ['POST', '/logout',              'AuthController@logout',         ['auth', 'csrf']],

    // ── Gated app ───────────────────────────────────────────────────────
    ['GET',  '/app',                       'DashboardController@index',   ['auth', 'sub']],

    ['GET',  '/app/learn',                 'WordController@learn',        ['auth', 'sub']],
    ['POST', '/app/learn/mark',            'WordController@markLearned',  ['auth', 'sub', 'csrf']],
    ['GET',  '/app/words',                 'WordController@index',        ['auth', 'sub']],
    ['GET',  '/app/words/{slug}',          'WordController@show',         ['auth', 'sub']],

    ['GET',  '/app/review',                'ReviewController@queue',      ['auth', 'sub']],
    ['POST', '/app/review/answer',         'ReviewController@answer',     ['auth', 'sub', 'csrf']],

    ['GET',  '/app/quiz',                  'QuizController@index',        ['auth', 'sub']],
    ['POST', '/app/quiz/submit',           'QuizController@submit',       ['auth', 'sub', 'csrf']],

    ['GET',  '/app/collection',            'CollectionController@index',  ['auth', 'sub']],

    ['GET',  '/app/guides',                'GuideController@appIndex',    ['auth', 'sub']],
    ['GET',  '/app/guides/{slug}',         'GuideController@appShow',     ['auth', 'sub']],

    ['GET',  '/app/calendar',              'CalendarController@index',    ['auth', 'sub']],

    ['GET',  '/app/qa',                    'QaController@index',          ['auth', 'sub']],
    ['GET',  '/app/qa/ask',                'QaController@askForm',        ['auth', 'sub']],
    ['POST', '/app/qa',                    'QaController@store',          ['auth', 'sub', 'csrf', 'rl:qa_post']],
    ['GET',  '/app/qa/{id}',               'QaController@show',           ['auth', 'sub']],

    // ── Account (auth only — reachable while expired, so billing can be fixed) ──
    ['GET',  '/account',             'AccountController@index',           ['auth']],
    ['GET',  '/account/unsubscribe', 'AccountController@unsubscribeForm', ['auth']],
    ['POST', '/account/unsubscribe', 'AccountController@unsubscribe',     ['auth', 'csrf']],
    ['POST', '/account/delete',      'AccountController@destroy',         ['auth', 'csrf']],
    ['GET',  '/expired',             'AccountController@expired',         ['auth']],

    // ── Webhooks (no CSRF — signature + IP allowlist instead) ───────────
    ['POST', '/webhooks/bdapps',     'WebhookController@bdapps',          []],

    // ── Admin ───────────────────────────────────────────────────────────
    ['GET',  '/admin/login',         'Admin/AdminAuthController@form',    []],
    ['POST', '/admin/login',         'Admin/AdminAuthController@login',   ['csrf', 'rl:admin_login']],
    ['POST', '/admin/logout',        'Admin/AdminAuthController@logout',  ['admin', 'csrf']],
    ['GET',  '/admin',               'Admin/AdminDashboardController@index', ['admin']],
    ['GET',  '/admin/logs',          'Admin/AdminDashboardController@logs',  ['admin']],

    ['GET',  '/admin/words',             'Admin/AdminWordController@index',   ['admin']],
    ['GET',  '/admin/words/new',         'Admin/AdminWordController@form',    ['admin']],
    ['POST', '/admin/words',             'Admin/AdminWordController@store',   ['admin', 'csrf']],
    ['GET',  '/admin/words/{id}',        'Admin/AdminWordController@form',    ['admin']],
    ['POST', '/admin/words/{id}',        'Admin/AdminWordController@update',  ['admin', 'csrf']],
    ['POST', '/admin/words/{id}/delete', 'Admin/AdminWordController@destroy', ['admin', 'csrf']],

    ['GET',  '/admin/guides',             'Admin/AdminGuideController@index',   ['admin']],
    ['GET',  '/admin/guides/new',         'Admin/AdminGuideController@form',    ['admin']],
    ['POST', '/admin/guides',             'Admin/AdminGuideController@store',   ['admin', 'csrf']],
    ['GET',  '/admin/guides/{id}',        'Admin/AdminGuideController@form',    ['admin']],
    ['POST', '/admin/guides/{id}',        'Admin/AdminGuideController@update',  ['admin', 'csrf']],
    ['POST', '/admin/guides/{id}/delete', 'Admin/AdminGuideController@destroy', ['admin', 'csrf']],

    ['GET',  '/admin/qa',                'Admin/AdminQaController@index',      ['admin']],
    ['GET',  '/admin/qa/{id}',           'Admin/AdminQaController@show',       ['admin']],
    ['POST', '/admin/qa/{id}',           'Admin/AdminQaController@update',     ['admin', 'csrf']],

    ['GET',  '/admin/users',             'Admin/AdminUserController@index',    ['admin']],
    ['GET',  '/admin/users/{id}',        'Admin/AdminUserController@show',     ['admin']],
    ['POST', '/admin/users/{id}',        'Admin/AdminUserController@update',   ['admin', 'csrf']],

    ['GET',  '/admin/contact',              'Admin/AdminContactController@index',   ['admin']],
    ['GET',  '/admin/contact/{id}',         'Admin/AdminContactController@show',    ['admin']],
    ['POST', '/admin/contact/{id}/resolve', 'Admin/AdminContactController@resolve', ['admin', 'csrf']],

    // ── Ops ─────────────────────────────────────────────────────────────
    ['GET',  '/health', 'HealthController@check', []],
];
