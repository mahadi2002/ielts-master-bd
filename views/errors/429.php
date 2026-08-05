<?php
/**
 * @var string|null $message
 * @var int|null    $retryAfter
 */
$this->layout('layouts/public', ['title' => 'অনেকবার চেষ্টা হয়েছে']);
?>
<section class="section text-center">
  <div class="wrap">
    <div class="empty-state">
      <div class="empty-state__icon">🐢</div>
      <h1><?= e($message ?: 'অনেকবার চেষ্টা হয়েছে। কিছুক্ষণ পর আবার চেষ্টা করুন।') ?></h1>
      <a href="/" class="btn btn--primary">হোমে ফিরে যান</a>
    </div>
  </div>
</section>
