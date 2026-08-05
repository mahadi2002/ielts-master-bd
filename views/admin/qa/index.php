<?php
/** @var array $open */
$this->layout('layouts/admin', ['title' => 'Q&A']);
?>
<h1 class="section-title">Open প্রশ্ন</h1>

<table class="data-table mt-20">
  <thead><tr><th>শিরোনাম</th><th>User</th><th>তারিখ</th></tr></thead>
  <tbody>
    <?php foreach ($open as $q): ?>
      <tr>
        <td><a href="/admin/qa/<?= (int) $q['id'] ?>" class="link-primary"><?= e($q['title']) ?></a></td>
        <td class="lang-en">****<?= e($q['msisdn_last4']) ?></td>
        <td><?= bn_date($q['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($open)): ?><tr><td colspan="3" class="text-center text-muted">কোনো Open প্রশ্ন নেই।</td></tr><?php endif; ?>
  </tbody>
</table>
