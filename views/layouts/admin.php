<?php
/**
 * @var string $content
 * @var array  $admin
 */
use App\Core\View;
use App\Repositories\ContactRepo;

$newContactCount = (new ContactRepo())->newCount();

$links = [
    ['/admin',         'Dashboard',      null],
    ['/admin/words',   'Words',          null],
    ['/admin/guides',  'Guides',         null],
    ['/admin/qa',      'Q&A',            null],
    ['/admin/contact', 'Contact Inbox',  $newContactCount > 0 ? $newContactCount : null],
    ['/admin/users',   'Users',          null],
    ['/admin/logs',    'Audit Log',      null],
];
?>
<!doctype html>
<html lang="bn" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Admin') . ' — Admin — ' . ($appName ?? 'IELTS Master BD')) ?></title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-sidebar__brand">
      <?= View::partial('partials/logo') ?>
      <span>Admin</span>
    </div>
    <nav>
      <?php foreach ($links as [$href, $label, $badge]): ?>
        <a href="<?= e($href) ?>"<?= $currentPath === $href ? ' aria-current="page"' : '' ?>>
          <?= e($label) ?><?php if ($badge !== null): ?> <span class="nav-badge"><?= (int) $badge ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <?php if (!empty($admin)): ?>
      <div class="admin-sidebar__user">
        <div><?= e($admin['name']) ?></div>
        <form method="post" action="/admin/logout" data-guard>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn--ghost btn--sm">Logout</button>
        </form>
      </div>
    <?php endif; ?>
  </aside>

  <main class="admin-main">
    <?= View::partial('partials/flash', ['notice' => $notice ?? null]) ?>
    <?= $content ?>
  </main>
</div>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
