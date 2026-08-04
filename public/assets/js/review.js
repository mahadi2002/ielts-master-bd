(function () {
  'use strict';

  var items = Array.prototype.slice.call(document.querySelectorAll('.js-review-item'));
  var doneEl = document.getElementById('js-review-done');
  if (!items.length) return;
  var idx = 0;

  items.forEach(function (item) {
    item.querySelectorAll('.rating-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var rating = btn.dataset.rating;
        var progressId = item.dataset.progressId;
        item.querySelectorAll('.rating-btn').forEach(function (b) { b.disabled = true; });
        btn.classList.add('is-selected');

        window.IMBD.post('/review/answer', { progress_id: progressId, rating: rating }).then(function () {
          items[idx].classList.remove('is-active');
          idx++;
          if (items[idx]) {
            items[idx].classList.add('is-active');
          } else if (doneEl) {
            doneEl.classList.remove('is-hidden');
          }
        });
      });
    });
  });
})();
