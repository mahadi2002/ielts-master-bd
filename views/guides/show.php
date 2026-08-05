<?php
/**
 * @var array $guide
 * @var bool  $isSubscribed
 */
use App\Core\View;
$this->layout('layouts/public');
$categoryLabel = config('content.guide_category.' . $guide['category'], $guide['category']);
?>
<section class="section">
  <div class="wrap max-w-640">
    <a href="/guides" class="text-muted">← সব Guide দেখুন</a>

    <div class="card mt-16">
      <span class="band-tag"><?= e((string) $categoryLabel) ?><?= $guide['band_relevance'] ? ' · Band ' . e($guide['band_relevance']) : '' ?></span>
      <h1 class="mt-8"><?= e($guide['title']) ?></h1>
      <p class="text-muted mt-10"><?= e($guide['excerpt']) ?></p>
    </div>

    <div class="mt-24">
      <?= View::partial('partials/paywall', ['label' => 'সম্পূর্ণ Guide পড়তে ও আরও অনেক Guide Access করতে Subscribe করুন।']) ?>
    </div>
  </div>
</section>
