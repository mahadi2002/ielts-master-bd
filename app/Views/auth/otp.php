<section class="section container text-center">
  <div class="card max-w-440 mi-auto">
    <h1 class="section-title">OTP যাচাই করুন</h1>
    <p class="section-sub mi-auto"><?= e($mobile) ?> নম্বরে একটি OTP পাঠানো হয়েছে। নিচে লিখুন।</p>

    <?php if (!empty($error)): ?>
      <div class="field-error mt-10"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($debugOtp)): ?>
      <div class="field-hint mt-10">🛠️ Dev OTP: <strong class="lang-en"><?= e($debugOtp) ?></strong></div>
    <?php endif; ?>

    <form action="/subscribe/otp/verify" method="post" class="stack mt-24">
      <?= csrf_field($csrfToken) ?>
      <input type="hidden" name="mobile_number" value="<?= e($mobile) ?>">
      <input type="hidden" name="otp" id="js-otp-value">

      <div class="otp-input-row">
        <?php for ($i = 0; $i < $otpLength; $i++): ?>
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" autocomplete="one-time-code">
        <?php endfor; ?>
      </div>

      <button type="submit" class="btn btn--primary btn--block btn--lg">যাচাই করুন</button>
    </form>

    <div class="mt-18">
      <form action="/subscribe/otp/send" method="post">
        <?= csrf_field($csrfToken) ?>
        <input type="hidden" name="mobile_number" value="<?= e($mobile) ?>">
        <button type="submit" id="js-resend-otp" class="btn btn--ghost btn--sm" data-cooldown="<?= (int) $resendCooldown ?>">আবার OTP পাঠান</button>
      </form>
      <p class="resend-timer" id="js-resend-timer"></p>
    </div>

    <p class="field-hint mt-14">
      ভুল নম্বর? <a href="/subscribe" class="link-primary">পরিবর্তন করুন</a>
    </p>
  </div>
</section>

<script src="<?= asset('js/otp.js') ?>"></script>
