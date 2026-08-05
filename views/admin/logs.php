<?php
/**
 * @var array $entries
 * @var int   $page
 */
$this->layout('layouts/admin', ['title' => 'Audit Log']);
?>
<h1 class="section-title">Audit Log</h1>

<table class="data-table mt-20">
  <thead><tr><th>Action</th><th>Actor</th><th>Entity</th><th>Meta</th><th>সময়</th></tr></thead>
  <tbody>
    <?php foreach ($entries as $row): ?>
      <tr>
        <td class="lang-en"><?= e($row['action']) ?></td>
        <td><?= e($row['actor_type']) ?><?= $row['actor_id'] ? ' #' . (int) $row['actor_id'] : '' ?></td>
        <td><?= e((string) ($row['entity'] ?? '—')) ?><?= $row['entity_id'] ? ' #' . (int) $row['entity_id'] : '' ?></td>
        <td class="fs-xs lang-en"><?= e((string) ($row['meta'] ?? '')) ?></td>
        <td><?= bn_date($row['created_at'], true) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($entries)): ?><tr><td colspan="5" class="text-center text-muted">কোনো এন্ট্রি নেই।</td></tr><?php endif; ?>
  </tbody>
</table>

<div class="d-actions mt-20">
  <?php if ($page > 1): ?><a href="/admin/logs?page=<?= $page - 1 ?>" class="btn btn--ghost btn--sm">← আগের পাতা</a><?php endif; ?>
  <a href="/admin/logs?page=<?= $page + 1 ?>" class="btn btn--ghost btn--sm">পরের পাতা →</a>
</div>
