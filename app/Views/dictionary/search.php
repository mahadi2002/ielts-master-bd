<section class="section container">
  <h1 class="section-title">IELTS Dictionary Search</h1>
  <p class="section-sub">Free — দিনে সীমিত সংখ্যক লুকআপ। পূর্ণ Access পেতে Subscribe করুন।</p>

  <form action="/dictionary" method="get" class="search-bar mt-20">
    <input type="text" name="q" placeholder="একটি Word লিখুন..." value="<?= e($query) ?>" autofocus>
    <button type="submit" class="btn btn--primary">খুঁজুন</button>
  </form>

  <?php if (!empty($rateLimited)): ?>
    <div class="flash flash--error rounded mt-16">
      আজকের ফ্রি লিমিট শেষ — <a href="/subscribe" class="link-underline">Subscribe করুন</a>।
    </div>
  <?php endif; ?>

  <?php if (empty($rateLimited) && $query !== '' && empty($results)): ?>
    <div class="empty-state">
      <div class="empty-state__icon">🤷</div>
      <p>"<?= e($query) ?>" এর জন্য কিছু পাওয়া যায়নি।</p>
    </div>
  <?php endif; ?>

  <div class="grid grid--3 mt-24">
    <?php foreach ($results as $w): ?>
      <a href="/dictionary/<?= e($w['headword']) ?>" class="card card--hover">
        <span class="band-tag">Band <?= (int) $w['ielts_band_level'] ?></span>
        <div class="lang-en headword-sm"><?= e($w['headword']) ?></div>
        <p class="text-muted mt-6"><?= e(mb_strimwidth($w['definition_bn'] ?: $w['definition_en'], 0, 80, '…')) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
