<?php
/**
 * @var array $guides
 * @var int   $total
 * @var int   $page
 * @var int   $limit
 */
$this->layout('layouts/admin', ['title' => 'Guides']);
$totalPages = max(1, (int) ceil($total / $limit));
?>
<div class="d-actions" style="justify-content:space-between;">
  <h1 class="section-title">Guides (<?= (int) $total ?>)</h1>
  <a href="/admin/guides/new" class="btn btn--primary">➕ নতুন Guide</a>
</div>

<table class="data-table mt-20">
  <thead><tr><th>Title</th><th>Category</th><th>Published</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($guides as $g): ?>
      <tr>
        <td><a href="/admin/guides/<?= (int) $g['id'] ?>" class="link-primary"><?= e($g['title']) ?></a></td>
        <td><?= e((string) config('content.guide_category.' . $g['category'], $g['category'])) ?></td>
        <td><?= $g['is_published'] ? '✓' : '—' ?></td>
        <td>
          <form action="/admin/guides/<?= (int) $g['id'] ?>/delete" method="post" class="js-confirm-delete" data-confirm="মুছে ফেলবেন?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--ghost btn--sm">মুছুন</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
  <div class="d-actions mt-20">
    <?php if ($page > 1): ?><a href="/admin/guides?page=<?= $page - 1 ?>" class="btn btn--ghost btn--sm">← আগের পাতা</a><?php endif; ?>
    <span class="text-muted fs-sm">পাতা <?= $page ?> / <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?><a href="/admin/guides?page=<?= $page + 1 ?>" class="btn btn--ghost btn--sm">পরের পাতা →</a><?php endif; ?>
  </div>
<?php endif; ?>
