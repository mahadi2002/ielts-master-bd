<?php /** @var array|null $word */ ?>
<?php $this->layout('layouts/app'); ?>
<h1 class="section-title">প্রশ্ন করুন</h1>
<?php if ($word): ?><p class="section-sub"><span class="lang-en"><?= e($word['headword']) ?></span> শব্দ নিয়ে প্রশ্ন করছেন।</p><?php endif; ?>

<?php if ($err = error_for('title')): ?>
  <div class="notice notice--error" role="alert"><span class="notice__icon">!</span><span><?= e($err) ?></span></div>
<?php endif; ?>

<form method="post" action="/app/qa" class="stack mt-20 max-w-520">
  <?= csrf_field() ?>
  <?php if ($word): ?><input type="hidden" name="word_slug" value="<?= e($word['slug']) ?>"><?php endif; ?>

  <div class="field">
    <label for="q-title">শিরোনাম</label>
    <input class="input" type="text" id="q-title" name="title" maxlength="200" required value="<?= e(old('title')) ?>">
  </div>
  <div class="field">
    <label for="q-body">বিস্তারিত</label>
    <textarea class="input" id="q-body" name="body" rows="6" maxlength="2000" required><?= e(old('body')) ?></textarea>
  </div>

  <button type="submit" class="btn btn--primary btn--block btn--lg">প্রশ্ন জমা দিন</button>
</form>
