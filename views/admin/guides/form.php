<?php
/** @var array|null $guide */
$this->layout('layouts/admin', ['title' => $guide ? 'Edit Guide' : 'New Guide']);
$action = $guide ? '/admin/guides/' . (int) $guide['id'] : '/admin/guides';
?>
<h1 class="section-title"><?= $guide ? 'Edit Guide' : 'New Guide' ?></h1>

<form action="<?= e($action) ?>" method="post" class="stack mt-20 max-w-640">
  <?= csrf_field() ?>
  <div class="field"><label>Title</label><input class="input" type="text" name="title" required value="<?= e($guide['title'] ?? old('title')) ?>"></div>
  <div class="field">
    <label>Category</label>
    <select class="input" name="category">
      <?php foreach (config('content.guide_category', []) as $key => $label): ?>
        <option value="<?= e($key) ?>"<?= ($guide['category'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>Band Relevance (e.g. "6-9")</label><input class="input" type="text" name="band_relevance" value="<?= e($guide['band_relevance'] ?? '') ?>"></div>
  <div class="field"><label>Excerpt (free teaser, max 300 chars)</label><textarea class="input" name="excerpt" maxlength="300" required><?= e($guide['excerpt'] ?? '') ?></textarea></div>
  <div class="field"><label>Body (Markdown — gated, full subscribers only)</label><textarea class="input" name="body_md" rows="14" required><?= e($guide['body_md'] ?? '') ?></textarea></div>
  <div class="field"><label><input type="checkbox" name="is_published" value="1" class="w-auto-inline"<?= !$guide || !empty($guide['is_published']) ? ' checked' : '' ?>> প্রকাশিত</label></div>
  <button type="submit" class="btn btn--primary"><?= $guide ? 'Update করুন' : 'যোগ করুন' ?></button>
</form>
