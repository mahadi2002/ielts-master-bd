<?php
/** @var string $currentPath */
use App\Core\Session;
use App\Services\SubscriptionService;

$userId    = Session::userId();
$hasAccess = $userId !== null && SubscriptionService::hasAccess($userId);
?>
<nav class="nav" aria-label="প্রধান মেনু">
  <a href="/dictionary"<?= str_starts_with($currentPath, '/dictionary') ? ' aria-current="page"' : '' ?>>Dictionary</a>
  <a href="/guides"<?= str_starts_with($currentPath, '/guides') ? ' aria-current="page"' : '' ?>>Guides</a>

  <?php if ($hasAccess): ?>
    <a href="/app" class="btn btn--primary btn--sm">আমার Dashboard</a>
  <?php elseif ($userId !== null): ?>
    <a href="/expired" class="btn btn--ghost btn--sm">Account</a>
  <?php else: ?>
    <a href="/login" class="link-muted">Login</a>
    <a href="/subscribe" class="btn btn--accent btn--sm">মাত্র ৳<?= e($dailyAmount ?? '2.78') ?>/day</a>
  <?php endif; ?>
</nav>
