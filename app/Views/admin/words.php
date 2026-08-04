<section class="section container">
  <h1 class="section-title">Admin · Words</h1>
  <p class="section-sub">মোট <?= (int) $total ?> টি শব্দ।</p>

  <div class="card m-24-0">
    <h3>নতুন শব্দ যোগ করুন</h3>
    <form action="/admin/words" method="post" class="grid grid--2 mt-12">
      <?= csrf_field($csrfToken) ?>
      <div class="field"><label>Headword</label><input type="text" name="headword" required></div>
      <div class="field"><label>IPA</label><input type="text" name="ipa"></div>
      <div class="field"><label>Part of Speech</label><input type="text" name="part_of_speech"></div>
      <div class="field">
        <label>Band Level</label>
        <select name="ielts_band_level">
          <option value="6">6</option><option value="7" selected>7</option><option value="8">8</option><option value="9">9</option>
        </select>
      </div>
      <div class="field grid-span-all"><label>Definition (English)</label><textarea name="definition_en" required></textarea></div>
      <div class="field grid-span-all"><label>Definition (Bangla)</label><textarea name="definition_bn"></textarea></div>
      <div class="field grid-span-all"><label>Example Sentence</label><input type="text" name="example_sentence"></div>
      <div class="field"><label>Synonyms (comma-separated)</label><input type="text" name="synonyms"></div>
      <div class="field">
        <label>Task Tag</label>
        <select name="task_tag">
          <option value="">—</option>
          <option value="task1">task1</option><option value="task2">task2</option>
          <option value="speaking">speaking</option><option value="general">general</option>
        </select>
      </div>
      <div class="field"><label>Frequency Rank</label><input type="number" name="frequency_rank"></div>
      <div class="field">
        <label><input type="checkbox" name="is_exclusive" value="1" class="w-auto-inline"> Exclusive (Reward Pool)</label>
      </div>
      <div class="grid-span-all"><button type="submit" class="btn btn--primary">যোগ করুন</button></div>
    </form>
  </div>

  <table class="data-table">
    <thead><tr><th>Headword</th><th>Band</th><th>Exclusive</th><th>Definition</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($words as $w): ?>
        <tr>
          <td class="lang-en"><?= e($w['headword']) ?></td>
          <td><?= (int) $w['ielts_band_level'] ?></td>
          <td><?= $w['is_exclusive'] ? '⭐' : '' ?></td>
          <td><?= e(mb_strimwidth($w['definition_en'], 0, 60, '…')) ?></td>
          <td>
            <form action="/admin/words/<?= e($w['id']) ?>/delete" method="post" class="js-confirm-delete" data-confirm="মুছে ফেলবেন?">
              <?= csrf_field($csrfToken) ?>
              <button type="submit" class="btn btn--ghost btn--sm">মুছুন</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
