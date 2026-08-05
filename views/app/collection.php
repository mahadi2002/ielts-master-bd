<?php /** @var array $words */ ?>
<?php $this->layout('layouts/app'); ?>
<h1 class="section-title">My Collection</h1>
<p class="section-sub">প্রতিদিনের লক্ষ্য পূরণ করে যেসব Exclusive শব্দ আনলক করেছেন।</p>

<?php if (empty($words)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">🏆</div>
    <p>এখনো কোনো Exclusive শব্দ আনলক হয়নি। আজকের লক্ষ্য পূরণ করে প্রথমটি আনলক করুন।</p>
    <a href="/app/learn" class="btn btn--primary">Learning শুরু করুন</a>
  </div>
<?php else: ?>
  <div class="grid grid--3 mt-24">
    <?php foreach ($words as $w): ?>
      <div class="card card--hover collection-card">
        <span class="band-tag band-tag--exclusive">Band <?= (int) $w['ielts_band_level'] ?></span>
        <div class="headword lang-en headword-md"><?= e($w['headword']) ?></div>
        <?php if ($w['ipa']): ?><div class="ipa text-muted lang-en"><?= e($w['ipa']) ?></div><?php endif; ?>
        <p><?= e($w['definition_bn'] ?: $w['definition_en']) ?></p>
        <?php if (!empty($w['synonyms'])): ?>
          <div class="synonym-list mt-10"><?php foreach ($w['synonyms'] as $syn): ?><span class="synonym-chip"><?= e($syn) ?></span><?php endforeach; ?></div>
        <?php endif; ?>
        <p class="text-muted fs-xs mt-12">আনলক: <?= e(date('d M, Y', strtotime($w['unlocked_at']))) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
