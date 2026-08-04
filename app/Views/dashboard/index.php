<section class="section container">
  <h1 class="section-title">Dashboard</h1>
  <p class="section-sub">আপনার শেখার অগ্রগতি একনজরে।</p>

  <div class="grid grid--4 mt-24">
    <div class="card stat-tile">
      <div class="stat-tile__value">🔥 <?= (int) $streak['current_streak'] ?></div>
      <div class="stat-tile__label">Current Streak</div>
    </div>
    <div class="card stat-tile">
      <div class="stat-tile__value"><?= (int) $streak['longest_streak'] ?></div>
      <div class="stat-tile__label">Longest Streak</div>
    </div>
    <div class="card stat-tile">
      <div class="stat-tile__value"><?= (int) $collectionCount ?></div>
      <div class="stat-tile__label">Exclusive Words</div>
    </div>
    <div class="card stat-tile">
      <div class="stat-tile__value"><?= e((string) $accuracy) ?>%</div>
      <div class="stat-tile__label">Quiz Accuracy</div>
    </div>
  </div>

  <div class="grid grid--2 mt-24">
    <div class="card">
      <h3>আজকের লক্ষ্য</h3>
      <div class="progress-ring progress-ring--light m-16-auto"
           data-target="<?= (int) $todayProgress['goal_target'] ?>" data-achieved="<?= (int) $todayProgress['goal_achieved'] ?>">
        <svg width="140" height="140" viewBox="0 0 140 140">
          <circle class="track" cx="70" cy="70" r="60" stroke="#E7E3DA"></circle>
          <circle class="fill" cx="70" cy="70" r="60" stroke="#1B4D3E"></circle>
        </svg>
        <div class="progress-ring__center">
          <span class="progress-ring__flame">🎯</span>
          <span class="progress-ring__count"><?= (int) $todayProgress['goal_achieved'] ?>/<?= (int) $todayProgress['goal_target'] ?></span>
        </div>
      </div>
      <p class="text-center text-muted">গত ৭ দিনে <?= (int) $completedThisWeek ?> বার লক্ষ্য পূর্ণ হয়েছে</p>
    </div>

    <div class="card">
      <h3>শব্দের অবস্থা</h3>
      <ul class="stack mt-12">
        <li class="weak-word-row"><span>নতুন (New)</span><strong><?= (int) $statusCounts['new'] ?></strong></li>
        <li class="weak-word-row"><span>শিখছি (Learning)</span><strong><?= (int) $statusCounts['learning'] ?></strong></li>
        <li class="weak-word-row"><span>Review-এ আছে</span><strong><?= (int) $statusCounts['review'] ?></strong></li>
        <li class="weak-word-row"><span>আয়ত্ত হয়েছে (Mastered)</span><strong><?= (int) $statusCounts['mastered'] ?></strong></li>
      </ul>
    </div>
  </div>

  <div class="grid grid--2 mt-24">
    <div class="card">
      <h3>সাপ্তাহিক কার্যক্রম</h3>
      <div class="bar-chart mt-20">
        <?php
          $max = 1;
          foreach ($weeklyActivity as $row) { $max = max($max, (int) $row['c']); }
        ?>
        <?php foreach ($weeklyActivity as $row): ?>
          <div class="bar-chart__col">
            <div class="bar-chart__bar" data-bar-height="<?= (int) round(((int) $row['c'] / $max) * 100) ?>"></div>
            <span class="bar-chart__label"><?= e(date('d/m', strtotime($row['d']))) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($weeklyActivity)): ?>
          <p class="text-muted">এখনো কোনো কার্যক্রম নেই।</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h3>দুর্বল শব্দ (Weak Words)</h3>
      <ul class="stack mt-12">
        <?php foreach ($weakWords as $w): ?>
          <li class="weak-word-row">
            <span class="lang-en"><?= e($w['headword']) ?></span>
            <span class="text-muted"><?= e($w['definition_bn']) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (empty($weakWords)): ?>
          <p class="text-muted">এখনো কোনো দুর্বল শব্দ চিহ্নিত হয়নি — চালিয়ে যান!</p>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</section>
