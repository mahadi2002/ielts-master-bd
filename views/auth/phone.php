<?php
/**
 * @var string $next
 * @var bool   $isLogin
 */
use App\Core\View;

$this->layout('layouts/public', ['title' => $isLogin ? 'Login করুন' : 'Subscribe করুন']);
?>
<section class="section">
  <div class="wrap grid grid--2 align-center">
    <div>
      <?php if (!$isLogin): ?>
        <h1 class="section-title">🚀 এখনই Start করুন — মাত্র ৳<?= e($dailyAmount ?? '2.78') ?>/day</h1>
        <p class="section-sub">Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>
        <ul class="stack mt-24">
          <li>✅ প্রতিদিন Daily Goal + Exclusive Word Reward</li>
          <li>✅ Spaced Repetition দিয়ে দীর্ঘমেয়াদী মনে রাখা</li>
          <li>✅ Band 6/7/8/9 অনুযায়ী সাজানো শব্দভাণ্ডার</li>
          <li>✅ Unlimited Quiz, Guide &amp; Progress Dashboard</li>
        </ul>
      <?php else: ?>
        <h1 class="section-title">আবার স্বাগতম</h1>
        <p class="section-sub">আগে থেকেই Subscribed থাকলে আপনার নম্বর দিয়ে সরাসরি ঢুকতে পারবেন।</p>
      <?php endif; ?>
    </div>

    <?= View::partial('partials/otp-box', get_defined_vars()) ?>
  </div>

  <div class="wrap prose center mt-20">
    <p class="fs-sm text-muted">
      নম্বরটি encrypted অবস্থায় রাখা হয় এবং শুধু subscription যাচাইয়ের জন্য ব্যবহৃত হয়।
      বিস্তারিত <a href="/privacy" class="link-primary">Privacy Policy</a>-তে।
    </p>
  </div>
</section>
