<?php
/** @var array $guide
 *  @var string $href
 */
$categoryLabel = config('content.guide_category.' . $guide['category'], $guide['category']);
?>
<a href="<?= e($href) ?>" class="card card--hover guide-card">
  <span class="band-tag"><?= e((string) $categoryLabel) ?></span>
  <h3 class="mt-8"><?= e($guide['title']) ?></h3>
  <p class="text-muted mt-6"><?= e($guide['excerpt']) ?></p>
</a>
