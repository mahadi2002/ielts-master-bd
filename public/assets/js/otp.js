(function () {
  'use strict';

  var codeInput = document.getElementById('otp-code');
  if (codeInput) {
    codeInput.addEventListener('input', function () {
      codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
    });
  }

  var resendBtn = document.querySelector('[data-resend-button]');
  var timerLabel = document.getElementById('resend-timer');
  var dataEl = document.getElementById('page-data');

  if (resendBtn && timerLabel && dataEl) {
    var pageData = {};
    try { pageData = JSON.parse(dataEl.textContent); } catch (e) { pageData = {}; }

    var wait = parseInt(pageData.resendWait || 0, 10);
    if (wait > 0) {
      startCooldown(wait);
    }

    function startCooldown(seconds) {
      resendBtn.disabled = true;
      var remaining = seconds;
      timerLabel.textContent = remaining + ' সেকেন্ড পর আবার পাঠাতে পারবেন';
      var interval = setInterval(function () {
        remaining--;
        if (remaining <= 0) {
          clearInterval(interval);
          resendBtn.disabled = false;
          timerLabel.textContent = '';
        } else {
          timerLabel.textContent = remaining + ' সেকেন্ড পর আবার পাঠাতে পারবেন';
        }
      }, 1000);
    }
  }
})();
