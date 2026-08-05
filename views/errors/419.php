<?php
/** @var string|null $message */
$this->layout('layouts/public', ['title' => 'সেশন মেয়াদ শেষ']);
?>
<section class="section text-center">
  <div class="wrap">
    <div class="empty-state">
      <div class="empty-state__icon">⏳</div>
      <h1><?= e($message ?: 'সেশন-এর মেয়াদ শেষ, আবার চেষ্টা করুন') ?></h1>
      <button type="button" class="btn btn--ghost" data-go-back>← আগের পাতায় ফিরে যান</button>
      <a href="/" class="btn btn--primary">হোমে ফিরে যান</a>
    </div>
  </div>
</section>
