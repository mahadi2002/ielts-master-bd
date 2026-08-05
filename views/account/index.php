<?php
/**
 * @var array      $user
 * @var array|null $sub
 */
$this->layout('layouts/public', ['title' => 'Account']);
$statusLabel = match ($sub['status'] ?? null) {
    'active' => 'সক্রিয়', 'grace' => 'গ্রেস পিরিয়ড', 'pending' => 'অপেক্ষমাণ',
    'expired' => 'মেয়াদ শেষ', 'unsubscribed' => 'বন্ধ করা হয়েছে', default => 'অজানা',
};
?>
<section class="section">
  <div class="wrap max-w-520 mi-auto">
    <h1 class="section-title">Account</h1>

    <div class="card mt-20">
      <p><strong>নম্বর:</strong> <span class="lang-en">*******<?= e($user['msisdn_last4']) ?></span></p>
      <p><strong>Operator:</strong> <?= e(ucfirst($user['operator'])) ?></p>
      <p><strong>Subscription Status:</strong> <?= e($statusLabel) ?></p>
      <?php if (!empty($sub['current_period_end'])): ?>
        <p><strong>মেয়াদ:</strong> <?= bn_date($sub['current_period_end'], true) ?> পর্যন্ত</p>
      <?php endif; ?>
    </div>

    <div class="d-actions mt-20">
      <?php if (($sub['status'] ?? null) !== 'active' && ($sub['status'] ?? null) !== 'grace'): ?>
        <a href="/subscribe" class="btn btn--accent">আবার Subscribe করুন</a>
      <?php endif; ?>
      <a href="/account/unsubscribe" class="btn btn--ghost">Unsubscribe করুন</a>
    </div>

    <form method="post" action="/account/delete" class="mt-24 js-confirm-delete"
          data-confirm="আপনার Account স্থায়ীভাবে Delete হয়ে যাবে। নিশ্চিত?">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn--ghost btn--sm text-error">Account সম্পূর্ণ Delete করুন</button>
    </form>
  </div>
</section>
