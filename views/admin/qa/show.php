<?php /** @var array $question */ ?>
<?php $this->layout('layouts/admin', ['title' => $question['title']]); ?>
<a href="/admin/qa" class="text-muted">← সব প্রশ্নে ফিরে যান</a>

<div class="card mt-16 max-w-640">
  <?php if ($question['headword']): ?><span class="band-tag lang-en"><?= e($question['headword']) ?></span><?php endif; ?>
  <h2 class="mt-8"><?= e($question['title']) ?></h2>
  <p class="mt-10"><?= nl2br(e($question['body'])) ?></p>

  <?php if ($question['status'] === 'answered'): ?>
    <div class="answer-block mt-20">
      <span class="band-tag band-tag--exclusive">উত্তর দেওয়া হয়েছে</span>
      <p class="mt-8"><?= nl2br(e($question['answer'])) ?></p>
    </div>
  <?php else: ?>
    <form method="post" action="/admin/qa/<?= (int) $question['id'] ?>" class="stack mt-20">
      <?= csrf_field() ?>
      <div class="field">
        <label for="answer">উত্তর লিখুন</label>
        <textarea class="input" id="answer" name="answer" rows="6" required><?= e(old('answer')) ?></textarea>
      </div>
      <button type="submit" class="btn btn--primary">উত্তর জমা দিন</button>
    </form>
  <?php endif; ?>
</div>
