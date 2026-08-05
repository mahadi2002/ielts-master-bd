<?php
/**
 * @var string      $masked
 * @var int         $resendWait
 * @var string|null $devOtp
 */
$this->layout('layouts/public', ['title' => 'OTP যাচাই', 'scripts' => ['otp.js']]);
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box">
      <h2>Code বসান</h2>
      <p class="sub"><span class="mono"><?= e($masked) ?></span> নম্বরে ৬ সংখ্যার একটি Code পাঠানো হয়েছে।</p>

      <?php if ($devOtp !== null): ?>
        <div class="notice notice--warn">
          <span class="notice__icon" aria-hidden="true">⚙</span>
          <span>Development mode — Code: <strong class="mono"><?= e((string) $devOtp) ?></strong>
            (অথবা <span class="mono">123456</span>)</span>
        </div>
      <?php endif; ?>

      <?php if ($error = error_for('code')): ?>
        <div class="notice notice--error" role="alert">
          <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="/subscribe/verify" data-guard>
        <?= csrf_field() ?>

        <div class="field<?= error_for('code') ? ' field--error' : '' ?>">
          <label for="otp-code">OTP Code</label>
          <input class="input input--code" type="text" id="otp-code" name="code"
                 inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                 pattern="[0-9]{6}" required placeholder="------">
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Verify করুন</button>
      </form>

      <hr>

      <form method="post" action="/subscribe/resend">
        <?= csrf_field() ?>
        <button class="btn btn--ghost btn--block" type="submit" data-resend-button>Code আবার পাঠান</button>
        <p class="help center" id="resend-timer" role="status"></p>
      </form>

      <p class="fs-sm center mb-0"><a href="/subscribe" class="link-primary">অন্য নম্বর দিয়ে চেষ্টা করুন</a></p>
    </div>
  </div>
</section>

<script type="application/json" id="page-data"><?= json_encode(['resendWait' => $resendWait]) ?></script>
