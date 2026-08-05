<?php
/**
 * @var string $query
 * @var array  $results
 */
use App\Core\View;
$this->layout('layouts/public', ['title' => 'Dictionary Search']);
?>
<section class="section">
  <div class="wrap">
    <h1 class="section-title">IELTS Dictionary Search</h1>
    <p class="section-sub">Free — দিনে সীমিত সংখ্যক লুকআপ। পূর্ণ Access পেতে Subscribe করুন।</p>

    <form action="/dictionary" method="get" class="search-bar mt-20">
      <input class="input" type="text" name="q" placeholder="একটি Word লিখুন..." value="<?= e($query) ?>" autofocus>
      <button type="submit" class="btn btn--primary">খুঁজুন</button>
    </form>

    <?php if ($query !== '' && empty($results)): ?>
      <div class="empty-state">
        <div class="empty-state__icon">🤷</div>
        <p>"<?= e($query) ?>" এর জন্য কিছু পাওয়া যায়নি।</p>
      </div>
    <?php endif; ?>

    <div class="grid grid--3 mt-24">
      <?php foreach ($results as $w): ?>
        <?= View::partial('partials/word-card', ['word' => $w, 'href' => '/dictionary/' . $w['slug']]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
