<section class="section container">
  <div class="grid grid--2 align-center">
    <div>
      <?php if ($expired): ?>
        <div class="card border-error mb-20">
          <strong class="text-error">আপনার Subscription-এর মেয়াদ শেষ হয়ে গেছে।</strong>
          <p class="text-muted mt-6">আবার Subscribe করে সব Content Access করুন।</p>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="card border-error mb-20">
          <strong class="text-error"><?= e($error) ?></strong>
        </div>
      <?php endif; ?>

      <h1 class="section-title">🚀 এখনই Start করুন — মাত্র ৳2.78/day</h1>
      <p class="section-sub">Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>

      <ul class="stack mt-24">
        <li>✅ প্রতিদিন Daily Goal + Exclusive Word Reward</li>
        <li>✅ Spaced Repetition দিয়ে দীর্ঘমেয়াদী মনে রাখা</li>
        <li>✅ Band 6/7/8/9 অনুযায়ী সাজানো শব্দভাণ্ডার</li>
        <li>✅ Unlimited Quiz &amp; Progress Dashboard</li>
      </ul>
    </div>

    <div class="subscribe-box">
      <h2>আপনার Robi বা Airtel Number দিন</h2>
      <p>Instant Access পাবেন সব IELTS Content-এ!</p>

      <form action="/subscribe/otp/send" method="post" class="stack mt-20">
        <?= csrf_field($csrfToken) ?>
        <div class="field">
          <label for="mobile-input">Mobile Number</label>
          <input type="tel" id="js-mobile-input" name="mobile_number" placeholder="01XXXXXXXXX"
                 inputmode="numeric" maxlength="11" value="<?= e($oldMobile) ?>" required autofocus>
          <span class="field-hint">শুধু Robi (016/019) ও Airtel (017) Number</span>
        </div>
        <div class="lightning">⚡</div>
        <p class="text-center fs-sm">Daily মাত্র ৳2.78 — যেকোনো সময় Unsubscribe করুন</p>
        <button type="submit" class="btn btn--accent btn--block btn--lg">OTP পাঠান →</button>
      </form>
    </div>
  </div>
</section>

<script src="<?= asset('js/otp.js') ?>"></script>
