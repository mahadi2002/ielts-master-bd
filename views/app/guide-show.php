<?php
/**
 * @var array  $guide
 * @var string $bodyHtml
 */
$this->layout('layouts/app');
$categoryLabel = config('content.guide_category.' . $guide['category'], $guide['category']);
?>
<a href="/app/guides" class="text-muted">← সব Guide দেখুন</a>

<article class="card mt-16 max-w-640 prose">
  <span class="band-tag"><?= e((string) $categoryLabel) ?><?= $guide['band_relevance'] ? ' · Band ' . e($guide['band_relevance']) : '' ?></span>
  <h1 class="mt-8"><?= e($guide['title']) ?></h1>
  <div class="mt-16"><?= $bodyHtml ?></div>
</article>
