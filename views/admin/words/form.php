<?php
/** @var array|null $word */
$this->layout('layouts/admin', ['title' => $word ? 'Edit Word' : 'New Word']);
$action = $word ? '/admin/words/' . (int) $word['id'] : '/admin/words';
$synonymsStr = $word && !empty($word['synonyms']) ? implode(', ', (array) $word['synonyms']) : '';
?>
<h1 class="section-title"><?= $word ? 'Edit Word' : 'New Word' ?></h1>

<form action="<?= e($action) ?>" method="post" class="grid grid--2 mt-20">
  <?= csrf_field() ?>
  <div class="field"><label>Headword</label><input class="input" type="text" name="headword" required value="<?= e($word['headword'] ?? old('headword')) ?>"></div>
  <div class="field"><label>IPA</label><input class="input" type="text" name="ipa" value="<?= e($word['ipa'] ?? '') ?>"></div>
  <div class="field"><label>Part of Speech</label><input class="input" type="text" name="part_of_speech" value="<?= e($word['part_of_speech'] ?? '') ?>"></div>
  <div class="field">
    <label>Band Level</label>
    <select class="input" name="ielts_band_level">
      <?php foreach ([6, 7, 8, 9] as $b): ?>
        <option value="<?= $b ?>"<?= ((int) ($word['ielts_band_level'] ?? 7)) === $b ? ' selected' : '' ?>><?= $b ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field grid-span-all"><label>Definition (English)</label><textarea class="input" name="definition_en" required><?= e($word['definition_en'] ?? '') ?></textarea></div>
  <div class="field grid-span-all"><label>Definition (Bangla)</label><textarea class="input" name="definition_bn"><?= e($word['definition_bn'] ?? '') ?></textarea></div>
  <div class="field grid-span-all"><label>Example Sentence</label><input class="input" type="text" name="example_sentence" value="<?= e($word['example_sentence'] ?? '') ?>"></div>
  <div class="field"><label>Synonyms (comma-separated)</label><input class="input" type="text" name="synonyms" value="<?= e($synonymsStr) ?>"></div>
  <div class="field">
    <label>Task Tag</label>
    <select class="input" name="task_tag">
      <option value="">—</option>
      <?php foreach (config('content.task_tag', []) as $key => $label): ?>
        <option value="<?= e($key) ?>"<?= ($word['task_tag'] ?? '') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>Frequency Rank</label><input class="input" type="number" name="frequency_rank" value="<?= e((string) ($word['frequency_rank'] ?? '')) ?>"></div>
  <div class="field">
    <label><input type="checkbox" name="is_exclusive" value="1" class="w-auto-inline"<?= !empty($word['is_exclusive']) ? ' checked' : '' ?>> Exclusive (Reward Pool)</label>
  </div>
  <div class="grid-span-all"><button type="submit" class="btn btn--primary"><?= $word ? 'Update করুন' : 'যোগ করুন' ?></button></div>
</form>
