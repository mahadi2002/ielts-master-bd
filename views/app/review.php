<?php
/** @var array $words */
$this->layout('layouts/app', ['scripts' => ['review.js']]);
?>
<h1 class="section-title">Review Queue</h1>
<p class="section-sub">Spaced Repetition অনুযায়ী আজ যেসব শব্দ Review করার সময় হয়েছে।</p>

<?php if (empty($words)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">🌿</div>
    <p>আজকের জন্য কোনো Review বাকি নেই। নতুন শব্দ শিখতে <a href="/app/learn" class="link-primary">Learn</a>-এ যান।</p>
  </div>
<?php else: ?>
  <div id="js-review-stage" class="max-w-520 mt-24">
    <?php foreach ($words as $i => $w): ?>
      <div class="card review-card js-review-item<?= $i === 0 ? ' is-active' : '' ?>" data-progress-id="<?= (int) $w['id'] ?>">
        <span class="band-tag">Review · <?= (int) $w['repetitions'] ?> repetitions</span>
        <div class="review-card__word lang-en"><?= e($w['headword']) ?></div>
        <p class="text-muted lang-en"><?= e($w['ipa']) ?></p>
        <p><?= e($w['definition_bn'] ?: $w['definition_en']) ?></p>
        <?php if ($w['example_sentence']): ?><p class="text-muted lang-en">"<?= e($w['example_sentence']) ?>"</p><?php endif; ?>

        <div class="rating-row mt-16">
          <button class="rating-btn rating-btn--again" data-rating="again">আবার</button>
          <button class="rating-btn rating-btn--hard" data-rating="hard">কঠিন</button>
          <button class="rating-btn rating-btn--good" data-rating="good">ভালো</button>
          <button class="rating-btn rating-btn--easy" data-rating="easy">সহজ</button>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="empty-state is-hidden" id="js-review-done">
      <div class="empty-state__icon">✅</div>
      <p>আজকের Review Queue শেষ!</p>
    </div>
  </div>
<?php endif; ?>
