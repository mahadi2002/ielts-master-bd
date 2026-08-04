(function () {
  'use strict';

  // ---- Mobile nav toggle ----
  var toggle = document.getElementById('js-nav-toggle');
  var nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

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

  // ---- Server-computed dynamic sizes (CSP-safe: JS DOM property, not an inline style attribute) ----
  document.querySelectorAll('[data-bar-height]').forEach(function (el) {
    el.style.height = el.dataset.barHeight + '%';
  });

  window.IMBD.pulseRing = function (el, newValue) {
    if (!el) return;
    if (el.paint) el.paint(newValue);
    el.classList.remove('progress-ring--pulse');
    void el.offsetWidth;
    el.classList.add('progress-ring--pulse');
  };

  // ---- Streak counter bump (header chip) ----
  window.IMBD.bumpStreak = function (value) {
    var el = document.getElementById('js-streak-count');
    if (el) el.textContent = value;
  };

  // ---- CSRF-aware fetch helper ----
  window.IMBD.post = function (url, data) {
    var body = new URLSearchParams(data || {});
    var tokenInput = document.querySelector('input[name="_csrf"]');
    if (tokenInput && !body.has('_csrf')) {
      body.set('_csrf', tokenInput.value);
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

  // ---- Confirm-before-submit forms (admin delete etc.) ----
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('.js-confirm-delete');
    if (form && !window.confirm(form.dataset.confirm || 'নিশ্চিত?')) {
      e.preventDefault();
    }
  });

  // ---- Exclusive-word unlock modal ----
  window.IMBD.showUnlockModal = function (word) {
    var backdrop = document.getElementById('js-unlock-modal');
    if (!backdrop || !word) return;
    backdrop.querySelector('[data-field="headword"]').textContent = word.headword || '';
    backdrop.querySelector('[data-field="definition_bn"]').textContent = word.definition_bn || word.definition_en || '';
    backdrop.classList.add('is-open');
  };
  document.addEventListener('click', function (e) {
    if (e.target.matches('[data-close-modal]') || e.target.classList.contains('modal-backdrop')) {
      var backdrop = document.getElementById('js-unlock-modal');
      if (backdrop) backdrop.classList.remove('is-open');
    }
  });
})();
