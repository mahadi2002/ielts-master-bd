<?php
/**
 * @var array $answered
 * @var array $myQuestions
 */
$this->layout('layouts/app');
$statusLabels = config('content.question_status', []);
?>
<h1 class="section-title">প্রশ্ন-উত্তর</h1>
<p class="section-sub">শব্দ বা IELTS নিয়ে প্রশ্ন করুন — Expert-রা উত্তর দেবেন।</p>

<div class="d-actions mt-16" style="justify-content:flex-start;">
  <a href="/app/qa/ask" class="btn btn--primary">➕ নতুন প্রশ্ন করুন</a>
</div>

<?php if (!empty($myQuestions)): ?>
  <h3 class="mt-24">আমার প্রশ্ন</h3>
  <div class="stack mt-12">
    <?php foreach ($myQuestions as $q): ?>
      <a href="/app/qa/<?= (int) $q['id'] ?>" class="card card--hover">
        <span class="status-pill status-pill--<?= $q['status'] === 'answered' ? 'resolved' : 'new' ?>"><?= e((string) ($statusLabels[$q['status']] ?? $q['status'])) ?></span>
        <h4 class="mt-8"><?= e($q['title']) ?></h4>
        <?php if ($q['headword']): ?><span class="text-muted fs-sm lang-en">নিয়ে: <?= e($q['headword']) ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<h3 class="mt-24">সাম্প্রতিক উত্তর দেওয়া প্রশ্ন</h3>
<div class="stack mt-12">
  <?php foreach ($answered as $q): ?>
    <a href="/app/qa/<?= (int) $q['id'] ?>" class="card card--hover">
      <h4><?= e($q['title']) ?></h4>
      <p class="text-muted mt-6"><?= e(str_excerpt($q['body'], 100)) ?></p>
    </a>
  <?php endforeach; ?>
  <?php if (empty($answered)): ?><p class="text-muted">এখনো কোনো প্রশ্নের উত্তর দেওয়া হয়নি।</p><?php endif; ?>
</div>
