<?php
/**
 * @var array  $messages
 * @var string $status
 * @var int    $newCount
 */
$this->layout('layouts/admin', ['title' => 'Contact Inbox']);
$tabs = ['new' => 'নতুন', 'read' => 'দেখা হয়েছে', 'resolved' => 'সমাধান হয়েছে', 'all' => 'সব'];
?>
<h1 class="section-title">Contact Inbox</h1>

<div class="chip-row mt-16">
  <?php foreach ($tabs as $key => $label): ?>
    <a href="/admin/contact?status=<?= e($key) ?>" class="chip<?= $status === $key ? ' chip--active' : '' ?>">
      <?= e($label) ?><?= $key === 'new' && $newCount > 0 ? ' (' . (int) $newCount . ')' : '' ?>
    </a>
  <?php endforeach; ?>
</div>

<table class="data-table mt-20">
  <thead><tr><th>নাম</th><th>যোগাযোগ</th><th>বার্তা</th><th>Status</th><th>তারিখ</th></tr></thead>
  <tbody>
    <?php foreach ($messages as $m): ?>
      <tr>
        <td><a href="/admin/contact/<?= (int) $m['id'] ?>" class="link-primary"><?= e($m['name']) ?></a></td>
        <td class="lang-en"><?= e($m['contact']) ?></td>
        <td><?= e(str_excerpt($m['message'], 60)) ?></td>
        <td><span class="status-pill status-pill--<?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
        <td><?= bn_date($m['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($messages)): ?>
      <tr><td colspan="5" class="text-center text-muted">কোনো বার্তা নেই।</td></tr>
    <?php endif; ?>
  </tbody>
</table>
