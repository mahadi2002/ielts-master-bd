<?php
/**
 * @var array       $guides
 * @var string|null $category
 */
use App\Core\View;
$this->layout('layouts/public', ['title' => 'Guides']);
$categories = config('content.guide_category', []);
?>
<section class="section">
  <div class="wrap">
    <h1 class="section-title">IELTS Guide লাইব্রেরি</h1>
    <p class="section-sub">Writing, Speaking, Reading, Listening ও Vocabulary কৌশল — বিনামূল্যে পড়ুন, সম্পূর্ণ বিস্তারিত পেতে Subscribe করুন।</p>

    <div class="chip-row mt-16">
      <a href="/guides" class="chip<?= $category === null ? ' chip--active' : '' ?>">সব</a>
      <?php foreach ($categories as $key => $label): ?>
        <a href="/guides?category=<?= e($key) ?>" class="chip<?= $category === $key ? ' chip--active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="grid grid--3 mt-24">
      <?php foreach ($guides as $g): ?>
        <?= View::partial('partials/guide-card', ['guide' => $g, 'href' => '/guides/' . $g['slug']]) ?>
      <?php endforeach; ?>
    </div>

    <?php if (empty($guides)): ?>
      <div class="empty-state"><div class="empty-state__icon">📚</div><p>এই Category-তে এখনো কোনো Guide নেই।</p></div>
    <?php endif; ?>
  </div>
</section>
