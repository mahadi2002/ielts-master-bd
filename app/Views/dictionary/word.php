<section class="section container">
  <a href="/dictionary" class="text-muted">← আবার Search করুন</a>

  <div class="card max-w-640 mt-16">
    <span class="band-tag">Band <?= (int) $word['ielts_band_level'] ?><?= $word['task_tag'] ? ' · ' . e($word['task_tag']) : '' ?></span>
    <div class="word-detail__headword"><?= e($word['headword']) ?></div>
    <?php if ($word['ipa']): ?><p class="text-muted lang-en"><?= e($word['ipa']) ?> <?= $word['part_of_speech'] ? '· ' . e($word['part_of_speech']) : '' ?></p><?php endif; ?>

    <p><?= e($word['definition_bn'] ?: $word['definition_en']) ?></p>
    <p class="text-muted lang-en"><?= e($word['definition_en']) ?></p>

    <?php if ($word['example_sentence']): ?>
      <p class="text-muted lang-en fs-italic">"<?= e($word['example_sentence']) ?>"</p>
    <?php endif; ?>

    <?php if (!empty($word['synonyms'])): ?>
      <h3 class="mt-20">Synonyms</h3>
      <div class="synonym-list">
        <?php foreach ($word['synonyms'] as $syn): ?>
          <span class="synonym-chip"><?= e($syn) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
