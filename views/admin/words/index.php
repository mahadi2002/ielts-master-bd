<?php
/**
 * @var array $words
 * @var int   $total
 * @var int   $page
 * @var int   $limit
 */
$this->layout('layouts/admin', ['title' => 'Words']);
$totalPages = max(1, (int) ceil($total / $limit));
?>
<div class="d-actions" style="justify-content:space-between;">
  <h1 class="section-title">Words (<?= (int) $total ?>)</h1>
  <a href="/admin/words/new" class="btn btn--primary">➕ নতুন শব্দ</a>
</div>

<table class="data-table mt-20">
  <thead><tr><th>Headword</th><th>Band</th><th>Exclusive</th><th>Definition</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($words as $w): ?>
      <tr>
        <td class="lang-en"><a href="/admin/words/<?= (int) $w['id'] ?>" class="link-primary"><?= e($w['headword']) ?></a></td>
        <td><?= (int) $w['ielts_band_level'] ?></td>
        <td><?= $w['is_exclusive'] ? '⭐' : '' ?></td>
        <td><?= e(str_excerpt($w['definition_en'], 60)) ?></td>
        <td>
          <form action="/admin/words/<?= (int) $w['id'] ?>/delete" method="post" class="js-confirm-delete" data-confirm="মুছে ফেলবেন?">
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
    <?php if ($page > 1): ?><a href="/admin/words?page=<?= $page - 1 ?>" class="btn btn--ghost btn--sm">← আগের পাতা</a><?php endif; ?>
    <span class="text-muted fs-sm">পাতা <?= $page ?> / <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?><a href="/admin/words?page=<?= $page + 1 ?>" class="btn btn--ghost btn--sm">পরের পাতা →</a><?php endif; ?>
  </div>
<?php endif; ?>
