<?php
/**
 * @var array $grid  {year, month, weeks}
 * @var array $prev
 * @var array $next
 */
$this->layout('layouts/app');
$monthNames = [1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
$dayLabels = ['সোম', 'মঙ্গল', 'বুধ', 'বৃহ', 'শুক্র', 'শনি', 'রবি'];
?>
<h1 class="section-title">Study Calendar</h1>
<p class="section-sub">যে দিনগুলোতে আজকের লক্ষ্য পূর্ণ করেছেন তা এখানে জ্বলজ্বল করবে।</p>

<div class="calendar-nav mt-20">
  <a href="/app/calendar?y=<?= $prev['y'] ?>&m=<?= $prev['m'] ?>" class="btn btn--ghost btn--sm">← আগের মাস</a>
  <h2><?= e($monthNames[$grid['month']]) ?> <?= bn_num($grid['year']) ?></h2>
  <a href="/app/calendar?y=<?= $next['y'] ?>&m=<?= $next['m'] ?>" class="btn btn--ghost btn--sm">পরের মাস →</a>
</div>

<div class="calendar-grid mt-16">
  <?php foreach ($dayLabels as $d): ?><div class="calendar-grid__head"><?= e($d) ?></div><?php endforeach; ?>

  <?php foreach ($grid['weeks'] as $week): ?>
    <?php foreach ($week as $cell): ?>
      <?php if ($cell === null): ?>
        <div class="calendar-cell calendar-cell--empty"></div>
      <?php else: ?>
        <div class="calendar-cell<?= $cell['completed'] ? ' calendar-cell--completed' : '' ?><?= $cell['isToday'] ? ' calendar-cell--today' : '' ?>">
          <span><?= bn_num($cell['day']) ?></span>
          <?php if ($cell['completed']): ?><span class="calendar-cell__flame" aria-hidden="true">🔥</span><?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>
