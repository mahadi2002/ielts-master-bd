<?php
/**
 * Gated app shell: left sidebar >=900px, bottom tab bar below.
 * Most of this audience is on a phone — this is a ৳2.78/day product.
 *
 * @var string $content
 */
use App\Core\Session;
use App\Core\View;
use App\Services\SubscriptionService;

$userId      = Session::userId();
$graceNotice = $userId === null ? null : SubscriptionService::graceNotice($userId);

$sideLinks = [
    'শেখা' => [
        ['/app',          'ড্যাশবোর্ড',   'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z'],
        ['/app/learn',    'Learn',        'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z'],
        ['/app/review',   'Review',       'M4 4v6h6M20 20v-6h-6M4.5 15a8 8 0 0 0 14.9 2M19.5 9a8 8 0 0 0-14.9-2'],
        ['/app/quiz',     'Quiz',         'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
    ],
    'জ্ঞানভাণ্ডার' => [
        ['/app/words',    'সব শব্দ',      'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z'],
        ['/app/collection', 'Collection', 'M12 2l2.9 6.6 7.1.6-5.4 4.7 1.7 7-6.3-3.9-6.3 3.9 1.7-7L2 9.2l7.1-.6z'],
        ['/app/guides',   'গাইড',        'M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3z M8 8h8M8 12h6'],
        ['/app/calendar', 'ক্যালেন্ডার', 'M4 6h16v15H4zM4 10h16M8 3v4M16 3v4'],
        ['/app/qa',       'প্রশ্ন-উত্তর',  'M12 19h.01M9.5 9a2.5 2.5 0 1 1 3.6 2.2c-.7.4-1.1 1-1.1 1.8v.5M4 5h16v13H8l-4 3z'],
    ],
];

$tabs = [
    ['/app',        'হোম',    'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z'],
    ['/app/learn',  'Learn',  'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z'],
    ['/app/review', 'Review', 'M4 4v6h6M20 20v-6h-6M4.5 15a8 8 0 0 0 14.9 2M19.5 9a8 8 0 0 0-14.9-2'],
    ['/app/quiz',   'Quiz',   'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
    ['/app/qa',     'প্রশ্ন', 'M4 5h16v13H8l-4 3z'],
];

$isCurrent = static function (string $href) use ($currentPath): bool {
    return $href === '/app' ? $currentPath === '/app' : str_starts_with($currentPath, $href);
};
?>
<!doctype html>
<html lang="bn" data-theme="<?= e($theme ?? 'light') ?>">
<head>
<?= View::partial('partials/head', get_defined_vars()) ?>
</head>
<body>
<a class="skip-link" href="#main">মূল অংশে যান</a>

<div class="app-shell">
  <header class="app-topbar">
    <div class="wrap app-topbar__inner">
      <a class="logo" href="/app">
        <?= View::partial('partials/logo') ?>
        <span><?= e($appName) ?></span>
      </a>

      <nav class="nav" aria-label="অ্যাকাউন্ট">
        <span class="streak-chip" title="Current streak">
          🔥 <span id="js-streak-count"><?= (int) ($streakCount ?? 0) ?></span>
        </span>
        <a href="/account"<?= $currentPath === '/account' ? ' aria-current="page"' : '' ?>>Account</a>
        <button class="theme-toggle" type="button" data-theme-toggle
                aria-label="<?= ($theme ?? 'light') === 'dark' ? 'দিনের রঙে দেখুন' : 'রাতের রঙে দেখুন' ?>">
          <?= ($theme ?? 'light') === 'dark' ? '☀' : '☾' ?>
        </button>
        <form method="post" action="/logout" data-guard>
          <?= csrf_field() ?>
          <button class="btn btn--ghost btn--sm" type="submit">Logout</button>
        </form>
      </nav>
    </div>
  </header>

  <div class="app-body">
    <aside class="app-sidebar">
      <?php foreach ($sideLinks as $group => $items): ?>
        <div class="side-nav">
          <p class="group-label"><?= e($group) ?></p>
          <?php foreach ($items as [$href, $label, $path]): ?>
            <a href="<?= e($href) ?>"<?= $isCurrent($href) ? ' aria-current="page"' : '' ?>>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg>
              <span><?= e($label) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </aside>

    <main class="app-main" id="main">
      <?php if ($graceNotice !== null): ?>
        <div class="notice notice--warn" role="alert">
          <span class="notice__icon" aria-hidden="true">⚠</span>
          <span><?= e($graceNotice) ?></span>
        </div>
      <?php endif; ?>

      <?= View::partial('partials/flash', ['notice' => $notice ?? null]) ?>
      <?= $content ?>
    </main>
  </div>

  <nav class="tabbar" aria-label="দ্রুত মেনু">
    <?php foreach ($tabs as [$href, $label, $path]): ?>
      <a href="<?= e($href) ?>"<?= $isCurrent($href) ? ' aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</div>

<div class="modal-backdrop" id="js-unlock-modal">
  <div class="modal">
    <div class="fs-24">🏆</div>
    <h2>অভিনন্দন! একটি নতুন শব্দ আনলক হয়েছে</h2>
    <div class="celebration__word" data-field="headword"></div>
    <p data-field="definition_bn"></p>
    <button class="btn btn--primary" data-close-modal>ঠিক আছে</button>
  </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<?php foreach (($scripts ?? []) as $script): ?>
  <script src="<?= e(asset('js/' . $script)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
