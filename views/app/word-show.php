<?php
/**
 * @var array      $word
 * @var array|null $progress
 */
$this->layout('layouts/app');
$statusLabels = config('content.word_status', []);
?>
<a href="/app/words" class="text-muted">← সব শব্দে ফিরে যান</a>

<div class="card mt-16 max-w-640">
  <span class="band-tag<?= $word['is_exclusive'] ? ' band-tag--exclusive' : '' ?>">
    Band <?= (int) $word['ielts_band_level'] ?><?= $word['is_exclusive'] ? ' · ⭐ Exclusive' : '' ?>
  </span>
  <div class="word-detail__headword lang-en"><?= e($word['headword']) ?></div>
  <?php if ($word['ipa']): ?><p class="text-muted lang-en"><?= e($word['ipa']) ?> <?= $word['part_of_speech'] ? '· ' . e($word['part_of_speech']) : '' ?></p><?php endif; ?>

  <p><?= e($word['definition_bn'] ?: $word['definition_en']) ?></p>
  <p class="text-muted lang-en"><?= e($word['definition_en']) ?></p>

  <?php if ($word['example_sentence']): ?><p class="text-muted lang-en fs-italic">"<?= e($word['example_sentence']) ?>"</p><?php endif; ?>

  <?php if (!empty($word['synonyms'])): ?>
    <h3 class="mt-20">Synonyms</h3>
    <div class="synonym-list"><?php foreach ($word['synonyms'] as $syn): ?><span class="synonym-chip"><?= e($syn) ?></span><?php endforeach; ?></div>
  <?php endif; ?>

  <?php if ($progress): ?>
    <p class="text-muted fs-sm mt-16">আপনার অবস্থা: <?= e((string) ($statusLabels[$progress['status']] ?? $progress['status'])) ?> · পরবর্তী Review: <?= bn_date($progress['next_review_date']) ?></p>
  <?php endif; ?>

  <div class="d-actions mt-20">
    <a href="/app/qa/ask?word=<?= e($word['slug']) ?>" class="btn btn--ghost btn--sm">এই শব্দ নিয়ে প্রশ্ন করুন</a>
  </div>
</div>
