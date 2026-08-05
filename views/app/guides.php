<?php
/**
 * @var array       $guides
 * @var string|null $category
 */
use App\Core\View;
$this->layout('layouts/app');
$categories = config('content.guide_category', []);
?>
<h1 class="section-title">Guide লাইব্রেরি</h1>
<p class="section-sub">Writing, Speaking, Reading, Listening ও Vocabulary কৌশল — সম্পূর্ণ Access।</p>

<div class="chip-row mt-16">
  <a href="/app/guides" class="chip<?= $category === null ? ' chip--active' : '' ?>">সব</a>
  <?php foreach ($categories as $key => $label): ?>
    <a href="/app/guides?category=<?= e($key) ?>" class="chip<?= $category === $key ? ' chip--active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<div class="grid grid--3 mt-24">
  <?php foreach ($guides as $g): ?>
    <?= View::partial('partials/guide-card', ['guide' => $g, 'href' => '/app/guides/' . $g['slug']]) ?>
  <?php endforeach; ?>
</div>
