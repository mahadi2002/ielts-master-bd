<?php
/** @var array $message */
$this->layout('layouts/admin', ['title' => 'Message #' . $message['id']]);
?>
<a href="/admin/contact" class="text-muted">← Inbox-এ ফিরে যান</a>

<div class="card mt-16 max-w-640">
  <span class="status-pill status-pill--<?= e($message['status']) ?>"><?= e($message['status']) ?></span>
  <h2 class="mt-8"><?= e($message['name']) ?></h2>
  <p class="text-muted lang-en"><?= e($message['contact']) ?></p>
  <p class="text-muted fs-sm"><?= bn_date($message['created_at'], true) ?></p>

  <p class="mt-16"><?= nl2br(e($message['message'])) ?></p>

  <?php if ($message['status'] !== 'resolved'): ?>
    <form method="post" action="/admin/contact/<?= (int) $message['id'] ?>/resolve" class="mt-16">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn--primary">সমাধান হয়েছে বলে চিহ্নিত করুন</button>
    </form>
  <?php else: ?>
    <p class="text-muted mt-16 fs-sm">সমাধান করা হয়েছে: <?= bn_date($message['resolved_at'], true) ?></p>
  <?php endif; ?>
</div>
