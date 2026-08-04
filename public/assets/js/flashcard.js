(function () {
  'use strict';

  var stage = document.getElementById('js-flashcard-stage');
  if (!stage) return;

  var wordsData = JSON.parse(stage.dataset.words || '[]');
  var goalTarget = parseInt(stage.dataset.goalTarget || '5', 10);
  var goalAchieved = parseInt(stage.dataset.goalAchieved || '0', 10);
  var index = 0;

  var card = document.getElementById('js-flashcard');
  var elHeadword = document.getElementById('js-fc-headword');
  var elPos = document.getElementById('js-fc-pos');
  var elIpa = document.getElementById('js-fc-ipa');
  var elDefEn = document.getElementById('js-fc-def-en');
  var elDefBn = document.getElementById('js-fc-def-bn');
  var elExample = document.getElementById('js-fc-example');
  var elProgressText = document.getElementById('js-fc-progress-text');
  var ring = document.getElementById('js-goal-ring');
  var btnKnow = document.getElementById('js-fc-know');
  var btnAgain = document.getElementById('js-fc-again');
  var doneState = document.getElementById('js-fc-done');

  function render() {
    if (index >= wordsData.length) {
      stage.style.display = 'none';
      if (doneState) doneState.style.display = 'block';
      return;
    }
    var w = wordsData[index];
    card.classList.remove('is-flipped');
    elHeadword.textContent = w.headword;
    elPos.textContent = w.part_of_speech || '';
    elIpa.textContent = w.ipa || '';
    elDefEn.textContent = w.definition_en || '';
    elDefBn.textContent = w.definition_bn || '';
    elExample.textContent = w.example_sentence || '';
    if (elProgressText) elProgressText.textContent = (index + 1) + ' / ' + wordsData.length + ' শব্দ';
  }

  card.addEventListener('click', function () {
    card.classList.toggle('is-flipped');
  });

  function mark(wordId) {
    window.IMBD.post('/learn/mark', { word_id: wordId }).then(function (res) {
      if (!res.ok) {
        window.IMBD.toast(res.data.error || 'একটি সমস্যা হয়েছে।');
        return;
      }
      goalAchieved = res.data.progress.achieved;
      if (ring) window.IMBD.pulseRing(ring, goalAchieved);
      if (res.data.justCompleted) {
        window.IMBD.toast('আজকের লক্ষ্য পূর্ণ হয়েছে! 🎉');
        if (res.data.unlockedWord) {
          window.IMBD.showUnlockModal(res.data.unlockedWord);
        }
      }
      index++;
      render();
    });
  }

  if (btnKnow) {
    btnKnow.addEventListener('click', function (e) {
      e.stopPropagation();
      mark(wordsData[index].id);
    });
  }
  if (btnAgain) {
    btnAgain.addEventListener('click', function (e) {
      e.stopPropagation();
      // "again" still counts as engaged-with for the day's goal, per learning-session UX.
      mark(wordsData[index].id);
    });
  }

  // ---- Basic swipe support ----
  var startX = null;
  stage.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
  stage.addEventListener('touchend', function (e) {
    if (startX === null) return;
    var deltaX = e.changedTouches[0].clientX - startX;
    if (Math.abs(deltaX) > 60 && wordsData[index]) {
      mark(wordsData[index].id);
    }
    startX = null;
  }, { passive: true });

  render();
})();
