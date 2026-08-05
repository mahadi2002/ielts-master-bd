<?php
/** @var array $word */
$this->layout('layouts/public');
?>
<section class="section">
  <div class="wrap max-w-640">
    <a href="/dictionary" class="text-muted">← আবার Search করুন</a>

    <div class="card mt-16">
      <span class="band-tag">Band <?= (int) $word['ielts_band_level'] ?><?= $word['task_tag'] ? ' · ' . e((string) config('content.task_tag.' . $word['task_tag'], $word['task_tag'])) : '' ?></span>
      <div class="word-detail__headword lang-en"><?= e($word['headword']) ?></div>
      <?php if ($word['ipa']): ?><p class="text-muted lang-en"><?= e($word['ipa']) ?> <?= $word['part_of_speech'] ? '· ' . e($word['part_of_speech']) : '' ?></p><?php endif; ?>

      <p><?= e($word['definition_bn'] ?: $word['definition_en']) ?></p>
      <p class="text-muted lang-en"><?= e($word['definition_en']) ?></p>

      <?php if ($word['example_sentence']): ?>
        <p class="text-muted lang-en fs-italic">"<?= e($word['example_sentence']) ?>"</p>
      <?php endif; ?>

      <?php if (!empty($word['synonyms'])): ?>
        <h3 class="mt-20">Synonyms</h3>
        <div class="synonym-list">
          <?php foreach ($word['synonyms'] as $syn): ?><span class="synonym-chip"><?= e($syn) ?></span><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="mt-24">
      <?= \App\Core\View::partial('partials/paywall', ['label' => 'এই শব্দটি Review Queue-তে যোগ করে SM-2 দিয়ে দীর্ঘমেয়াদী মনে রাখতে Subscribe করুন।']) ?>
    </div>
  </div>
</section>
