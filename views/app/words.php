<?php
/**
 * @var array    $words
 * @var int|null $band
 * @var int      $page
 * @var int      $total
 * @var int      $limit
 */
use App\Core\View;
$this->layout('layouts/app');
$totalPages = max(1, (int) ceil($total / $limit));
?>
<h1 class="section-title">সব শব্দ</h1>
<p class="section-sub">Band অনুযায়ী পুরো Catalog Browse করুন।</p>

<div class="chip-row mt-16">
  <a href="/app/words" class="chip<?= $band === null ? ' chip--active' : '' ?>">সব</a>
  <?php foreach ([6, 7, 8, 9] as $b): ?>
    <a href="/app/words?band=<?= $b ?>" class="chip<?= $band === $b ? ' chip--active' : '' ?>">Band <?= $b ?></a>
  <?php endforeach; ?>
</div>

<div class="grid grid--3 mt-24">
  <?php foreach ($words as $w): ?>
    <?= View::partial('partials/word-card', ['word' => $w, 'href' => '/app/words/' . $w['slug']]) ?>
  <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
  <div class="d-actions mt-24">
    <?php if ($page > 1): ?><a href="/app/words?page=<?= $page - 1 ?><?= $band ? '&band=' . $band : '' ?>" class="btn btn--ghost btn--sm">← আগের পাতা</a><?php endif; ?>
    <span class="text-muted fs-sm">পাতা <?= $page ?> / <?= $totalPages ?></span>
    <?php if ($page < $totalPages): ?><a href="/app/words?page=<?= $page + 1 ?><?= $band ? '&band=' . $band : '' ?>" class="btn btn--ghost btn--sm">পরের পাতা →</a><?php endif; ?>
  </div>
<?php endif; ?>
