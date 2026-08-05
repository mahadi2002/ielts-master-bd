<?php
/** @var array $word
 *  @var string $href
 */
?>
<a href="<?= e($href) ?>" class="card card--hover word-card">
  <span class="band-tag<?= !empty($word['is_exclusive']) ? ' band-tag--exclusive' : '' ?>">
    Band <?= (int) $word['ielts_band_level'] ?><?= !empty($word['is_exclusive']) ? ' · ⭐' : '' ?>
  </span>
  <div class="word-card__headword lang-en"><?= e($word['headword']) ?></div>
  <?php if (!empty($word['ipa'])): ?><div class="ipa text-muted lang-en"><?= e($word['ipa']) ?></div><?php endif; ?>
  <p class="text-muted mt-6"><?= e(str_excerpt($word['definition_bn'] ?: $word['definition_en'], 80)) ?></p>
</a>
