<?php
/** @var string|null $label */
?>
<div class="paywall">
  <div class="paywall__icon" aria-hidden="true">🔒</div>
  <h3>পুরোটা পড়তে Subscribe করুন</h3>
  <p class="text-muted"><?= e($label ?? 'সম্পূর্ণ Content, Quiz, ও Progress Tracking আনলক করুন — মাত্র ৳' . ($dailyAmount ?? '2.78') . '/day।') ?></p>
  <a href="/subscribe" class="btn btn--accent btn--lg">🚀 এখনই Subscribe করুন</a>
</div>
