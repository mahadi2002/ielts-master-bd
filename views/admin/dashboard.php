<?php
/**
 * @var int   $wordCount
 * @var int   $userCount
 * @var int   $newUsers7d
 * @var int   $openQuestions
 * @var array $subStatuses
 * @var array $chargesToday
 * @var array $recentFailures
 * @var array $pools
 * @var array $alerts
 */
$this->layout('layouts/admin', ['title' => 'Dashboard']);
?>
<h1 class="section-title">Admin Dashboard</h1>

<div class="grid grid--4 mt-20">
  <div class="card stat-tile"><div class="stat-tile__value"><?= (int) $userCount ?></div><div class="stat-tile__label">Active Subscribers</div></div>
  <div class="card stat-tile"><div class="stat-tile__value"><?= (int) $newUsers7d ?></div><div class="stat-tile__label">নতুন (৭ দিন)</div></div>
  <div class="card stat-tile"><div class="stat-tile__value"><?= (int) $wordCount ?></div><div class="stat-tile__label">মোট শব্দ</div></div>
  <div class="card stat-tile"><div class="stat-tile__value"><?= (int) $openQuestions ?></div><div class="stat-tile__label">Open প্রশ্ন</div></div>
</div>

<?php if (!empty($alerts)): ?>
  <div class="mt-20">
    <?php foreach ($alerts as $alert): ?>
      <?php $ctx = json_decode($alert['context'] ?? '{}', true); ?>
      <div class="alert-row">
        <span>⚠️ <?= e($alert['alert_type']) ?> — Band <?= e((string) ($ctx['band'] ?? '?')) ?>
          <?php if (isset($ctx['remaining'])): ?>(অবশিষ্ট: <?= (int) $ctx['remaining'] ?>)<?php endif; ?>
        </span>
        <span class="text-muted fs-xs"><?= bn_date($alert['created_at']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="grid grid--2 mt-20">
  <div class="card">
    <h3>Subscription Status</h3>
    <ul class="stack mt-10">
      <?php foreach ($subStatuses as $status => $count): ?>
        <li class="weak-word-row"><span><?= e($status) ?></span><strong><?= (int) $count ?></strong></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card">
    <h3>আজকের Charge</h3>
    <ul class="stack mt-10">
      <?php foreach ($chargesToday as $status => $count): ?>
        <li class="weak-word-row"><span><?= e($status) ?></span><strong><?= (int) $count ?></strong></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<div class="card mt-20">
  <h3>Exclusive Word Pool</h3>
  <div class="grid grid--4 mt-16">
    <?php foreach ($pools as $pool): ?>
      <div class="stat-tile <?= (int) $pool['unclaimed'] < 14 ? 'card--accent' : '' ?>">
        <div class="stat-tile__value"><?= (int) $pool['unclaimed'] ?> / <?= (int) $pool['total'] ?></div>
        <div class="stat-tile__label">Band <?= (int) $pool['band'] ?> Unclaimed</div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!empty($recentFailures)): ?>
  <div class="card mt-20">
    <h3>সাম্প্রতিক Charge ব্যর্থতা</h3>
    <table class="data-table mt-10">
      <thead><tr><th>User</th><th>Amount</th><th>Code</th><th>সময়</th></tr></thead>
      <tbody>
        <?php foreach ($recentFailures as $f): ?>
          <tr>
            <td class="lang-en">****<?= e($f['msisdn_last4']) ?></td>
            <td>৳<?= e($f['amount']) ?></td>
            <td><?= e($f['status_code']) ?></td>
            <td><?= bn_date($f['attempted_at'], true) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
