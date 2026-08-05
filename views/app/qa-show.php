<?php /** @var array $question */ ?>
<?php $this->layout('layouts/app'); ?>
<a href="/app/qa" class="text-muted">← সব প্রশ্নে ফিরে যান</a>

<div class="card mt-16 max-w-640">
  <?php if ($question['headword']): ?><span class="band-tag lang-en"><?= e($question['headword']) ?></span><?php endif; ?>
  <h1 class="mt-8"><?= e($question['title']) ?></h1>
  <p class="mt-10"><?= nl2br(e($question['body'])) ?></p>

  <?php if ($question['status'] === 'answered'): ?>
    <div class="answer-block mt-20">
      <span class="band-tag band-tag--exclusive">উত্তর</span>
      <p class="mt-8"><?= nl2br(e($question['answer'])) ?></p>
      <p class="text-muted fs-xs mt-8">— IELTS Master BD Team, <?= bn_date($question['answered_at']) ?></p>
    </div>
  <?php else: ?>
    <div class="empty-state mt-20">
      <div class="empty-state__icon">⏳</div>
      <p>এই প্রশ্নের উত্তর এখনো দেওয়া হয়নি।</p>
    </div>
  <?php endif; ?>
</div>
