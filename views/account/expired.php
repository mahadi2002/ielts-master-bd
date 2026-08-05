<?php
/** @var array|null $sub */
$this->layout('layouts/public', ['title' => 'Subscription Expired']);
$isPending = ($sub['status'] ?? null) === 'pending';
?>
<section class="section">
  <div class="wrap max-w-520 mi-auto text-center">
    <div class="fs-24">⏳</div>
    <h1 class="section-title">
      <?= $isPending ? 'আপনার Subscription এখনো Active হয়নি' : 'আপনার Subscription-এর মেয়াদ শেষ হয়ে গেছে' ?>
    </h1>
    <p class="section-sub">
      <?php if ($isPending): ?>
        প্রথম Charge সম্পন্ন হয়নি — সাধারণত ব্যালেন্স কম থাকলে এমন হয়। Recharge করে আবার চেষ্টা করুন।
      <?php else: ?>
        Access ফিরে পেতে আবার Subscribe করুন — মাত্র ৳<?= e($dailyAmount ?? '2.78') ?>/day।
      <?php endif; ?>
    </p>

    <!-- Never a dead end: every "not active" screen offers both a path forward and a path out. -->
    <div class="d-actions mt-24">
      <a href="/subscribe" class="btn btn--accent btn--lg">🚀 আবার Subscribe করুন</a>
      <a href="/account/unsubscribe" class="btn btn--ghost">সম্পূর্ণ Unsubscribe করুন</a>
    </div>

    <p class="fs-sm mt-20"><a href="/contact" class="link-primary">সমস্যা হচ্ছে? যোগাযোগ করুন</a></p>
  </div>
</section>
