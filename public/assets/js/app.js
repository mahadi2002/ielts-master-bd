(function () {
  'use strict';

  // ---- Theme toggle ----
  var THEME_COOKIE = 'imbd_theme';
  function setThemeCookie(value) {
    document.cookie = THEME_COOKIE + '=' + value + '; path=/; max-age=31536000; SameSite=Lax';
  }
  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.dataset.theme === 'dark' ? 'light' : 'dark';
      root.dataset.theme = next;
      setThemeCookie(next);
      btn.textContent = next === 'dark' ? '☀' : '☾';
    });
  });

  // ---- Double-submit guard on forms marked data-guard ----
  document.querySelectorAll('form[data-guard]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!btn || btn.dataset.busy === 'true') return;

      btn.dataset.busy = 'true';
      btn.dataset.label = btn.textContent;
      btn.textContent = 'একটু অপেক্ষা করুন…';
      btn.setAttribute('aria-disabled', 'true');

      window.addEventListener('pageshow', function () {
        btn.dataset.busy = 'false';
        btn.textContent = btn.dataset.label || btn.textContent;
        btn.removeAttribute('aria-disabled');
      }, { once: true });
    });
  });

  // ---- Confirm-before-submit forms (admin delete, account delete) ----
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('.js-confirm-delete');
    if (form && !window.confirm(form.dataset.confirm || 'নিশ্চিত?')) {
      e.preventDefault();
    }
  });

  // ---- Generic "go back" button (CSP forbids javascript: URIs) ----
  document.querySelectorAll('[data-go-back]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (window.history.length > 1) window.history.back();
      else window.location.href = '/';
    });
  });

  // ---- Toast ----
  function toast(message, ms) {
    var el = document.createElement('div');
    el.className = 'toast';
    el.setAttribute('role', 'status');
    el.textContent = message;
    document.body.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('is-visible'); });
    setTimeout(function () {
      el.classList.remove('is-visible');
      setTimeout(function () { el.remove(); }, 220);
    }, ms || 3200);
  }
  window.IMBD = window.IMBD || {};
  window.IMBD.toast = toast;

  // ---- Progress ring ----
  function initProgressRing(el) {
    var target = parseInt(el.dataset.target || '1', 10);
    var achieved = parseInt(el.dataset.achieved || '0', 10);
    var circle = el.querySelector('.fill');
    if (!circle) return;
    var radius = circle.r.baseVal.value;
    var circumference = 2 * Math.PI * radius;
    circle.style.strokeDasharray = circumference + ' ' + circumference;

    function paint(value) {
      var ratio = Math.max(0, Math.min(1, target > 0 ? value / target : 0));
      var offset = circumference - ratio * circumference;
      circle.style.strokeDashoffset = offset;
      var countEl = el.querySelector('.progress-ring__count');
      if (countEl) countEl.textContent = value + '/' + target;
    }

    el.paint = paint;
    paint(achieved);
  }
  document.querySelectorAll('.progress-ring[data-target]').forEach(initProgressRing);

  window.IMBD.pulseRing = function (el, newValue) {
    if (!el) return;
    if (el.paint) el.paint(newValue);
    el.classList.remove('progress-ring--pulse');
    void el.offsetWidth;
    el.classList.add('progress-ring--pulse');
  };

  window.IMBD.bumpStreak = function (value) {
    var el = document.getElementById('js-streak-count');
    if (el) el.textContent = value;
  };

  // ---- Server-computed dynamic sizes (CSP-safe: JS DOM property, not an inline style attribute) ----
  document.querySelectorAll('[data-bar-height]').forEach(function (el) {
    el.style.height = el.dataset.barHeight + '%';
  });

  // ---- CSRF-aware fetch helper ----
  window.IMBD.post = function (url, data) {
    var body = new URLSearchParams(data || {});
    var tokenInput = document.querySelector('input[name="_token"]');
    if (tokenInput && !body.has('_token')) {
      body.set('_token', tokenInput.value);
    }
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: body.toString(),
    }).then(function (res) {
      return res.json().then(function (json) {
        return { ok: res.ok, status: res.status, data: json };
      });
    });
  };

  // ---- Exclusive-word unlock modal ----
  window.IMBD.showUnlockModal = function (word) {
    var backdrop = document.getElementById('js-unlock-modal');
    if (!backdrop || !word) return;
    var headwordEl = backdrop.querySelector('[data-field="headword"]');
    var defEl = backdrop.querySelector('[data-field="definition_bn"]');
    if (headwordEl) headwordEl.textContent = word.headword || '';
    if (defEl) defEl.textContent = word.definition_bn || word.definition_en || '';
    backdrop.classList.add('is-open');
  };
  document.addEventListener('click', function (e) {
    if (e.target.matches('[data-close-modal]') || e.target.classList.contains('modal-backdrop')) {
      var backdrop = document.getElementById('js-unlock-modal');
      if (backdrop) backdrop.classList.remove('is-open');
    }
  });

  // ---- Mobile number input mask (digits only, 11 chars) ----
  var msisdnInput = document.getElementById('msisdn');
  if (msisdnInput) {
    msisdnInput.addEventListener('input', function () {
      msisdnInput.value = msisdnInput.value.replace(/\D/g, '').slice(0, 11);
    });
  }
})();
