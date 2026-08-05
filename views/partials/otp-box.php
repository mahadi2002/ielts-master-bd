<?php
/**
 * @var string $next
 * @var bool   $isLogin
 */
?>
<div class="otp-box subscribe-box">
  <?php if ($isLogin): ?>
    <h2>আপনার নম্বর দিন</h2>
    <p>আগে থেকেই Subscribed? Login করুন।</p>
  <?php else: ?>
    <h2>আপনার Robi বা Airtel Number দিন</h2>
    <p>Instant Access পাবেন সব IELTS Content-এ!</p>
  <?php endif; ?>

  <?php if ($error = error_for('msisdn')): ?>
    <div class="notice notice--error" role="alert">
      <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="post" action="/subscribe/otp" data-guard class="stack mt-20">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <!-- Honeypot: hidden from real users via CSS, a bot filling this gets silently redirected. -->
    <div class="hp-field" aria-hidden="true">
      <label for="website">Website</label>
      <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="field<?= error_for('msisdn') ? ' field--error' : '' ?>">
      <label for="msisdn">Mobile Number</label>
      <input class="input" type="tel" id="msisdn" name="msisdn" placeholder="01XXXXXXXXX"
             inputmode="numeric" maxlength="11" value="<?= e(old('msisdn')) ?>" required autofocus>
      <span class="field-hint"><?= e($operatorNote ?? '') ?></span>
    </div>

    <div class="lightning" aria-hidden="true">⚡</div>
    <p class="text-center fs-sm">Daily মাত্র ৳<?= e($dailyAmount ?? '2.78') ?> — যেকোনো সময় Unsubscribe করুন</p>

    <button type="submit" class="btn btn--accent btn--block btn--lg">OTP পাঠান →</button>
  </form>
</div>
