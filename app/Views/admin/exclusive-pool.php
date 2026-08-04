<section class="section container">
  <h1 class="section-title">Admin · Exclusive Word Pool</h1>
  <p class="section-sub">প্রতিটি Band-এর জন্য কতটি Exclusive শব্দ বাকি আছে তা মনিটর করুন।</p>

  <?php if (!empty($alerts)): ?>
    <div class="mt-20">
      <?php foreach ($alerts as $alert): ?>
        <?php $ctx = json_decode($alert['context'] ?? '{}', true); ?>
        <div class="alert-row">
          <span>⚠️ <?= e($alert['alert_type']) ?> — Band <?= e((string) ($ctx['band'] ?? '?')) ?>
            <?php if (isset($ctx['remaining'])): ?>(অবশিষ্ট: <?= (int) $ctx['remaining'] ?>)<?php endif; ?>
          </span>
          <span class="text-muted"><?= e(date('d M, H:i', strtotime($alert['created_at']))) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="grid grid--4 mt-24">
    <?php foreach (($pools ?? []) as $pool): ?>
      <div class="card stat-tile <?= (int) $pool['unclaimed'] < 14 ? 'card--accent' : '' ?>">
        <div class="stat-tile__value"><?= (int) $pool['unclaimed'] ?> / <?= (int) $pool['total'] ?></div>
        <div class="stat-tile__label">Band <?= (int) $pool['band'] ?> Unclaimed</div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($pools)): ?>
    <p class="text-muted mt-20">এখনো কোনো Exclusive শব্দ যোগ করা হয়নি। <a href="/admin/words" class="link-primary">Words</a> থেকে যোগ করুন।</p>
  <?php endif; ?>
</section>
