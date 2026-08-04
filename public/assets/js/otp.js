(function () {
  'use strict';

  // ---- Mobile number input mask (digits only, 11 chars) ----
  var mobileInput = document.getElementById('js-mobile-input');
  if (mobileInput) {
    mobileInput.addEventListener('input', function () {
      mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 11);
    });
  }

  // ---- OTP digit boxes -> combine into hidden field ----
  var otpBoxes = Array.prototype.slice.call(document.querySelectorAll('.otp-digit'));
  var otpHidden = document.getElementById('js-otp-value');
  if (otpBoxes.length) {
    otpBoxes.forEach(function (box, i) {
      box.addEventListener('input', function () {
        box.value = box.value.replace(/\D/g, '').slice(0, 1);
        if (box.value && otpBoxes[i + 1]) otpBoxes[i + 1].focus();
        syncOtp();
      });
      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !box.value && otpBoxes[i - 1]) {
          otpBoxes[i - 1].focus();
        }
      });
      box.addEventListener('paste', function (e) {
        var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        if (!text) return;
        e.preventDefault();
        otpBoxes.forEach(function (b, idx) { b.value = text[idx] || ''; });
        syncOtp();
        var next = otpBoxes[Math.min(text.length, otpBoxes.length - 1)];
        if (next) next.focus();
      });
    });
  }
  function syncOtp() {
    if (otpHidden) {
      otpHidden.value = otpBoxes.map(function (b) { return b.value; }).join('');
    }
  }

  // ---- Resend cooldown timer ----
  var resendBtn = document.getElementById('js-resend-otp');
  var timerLabel = document.getElementById('js-resend-timer');
  if (resendBtn && timerLabel) {
    var cooldown = parseInt(resendBtn.dataset.cooldown || '60', 10);
    startCooldown(cooldown);

    function startCooldown(seconds) {
      resendBtn.disabled = true;
      var remaining = seconds;
      timerLabel.textContent = remaining + 's পর আবার পাঠাতে পারবেন';
      var interval = setInterval(function () {
        remaining--;
        if (remaining <= 0) {
          clearInterval(interval);
          resendBtn.disabled = false;
          timerLabel.textContent = '';
        } else {
          timerLabel.textContent = remaining + 's পর আবার পাঠাতে পারবেন';
        }
      }, 1000);
    }
  }
})();
