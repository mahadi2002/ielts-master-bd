(function () {
  'use strict';

  var stage = document.getElementById('js-quiz-stage');
  if (!stage) return;

  var quizzes = JSON.parse(stage.dataset.quizzes || '[]');
  var index = 0;
  var correctCount = 0;
  var answered = false;

  var elQuestion = document.getElementById('js-quiz-question');
  var elOptions = document.getElementById('js-quiz-options');
  var elProgressFill = document.getElementById('js-quiz-progress-fill');
  var elProgressText = document.getElementById('js-quiz-progress-text');
  var elResultBanner = document.getElementById('js-quiz-result-banner');
  var elNextBtn = document.getElementById('js-quiz-next');
  var doneState = document.getElementById('js-quiz-done');
  var elScore = document.getElementById('js-quiz-score');

  function render() {
    if (index >= quizzes.length) {
      stage.style.display = 'none';
      if (doneState) {
        doneState.style.display = 'block';
        if (elScore) elScore.textContent = correctCount + ' / ' + quizzes.length;
      }
      return;
    }

    answered = false;
    var q = quizzes[index];
    elResultBanner.style.display = 'none';
    elNextBtn.style.display = 'none';
    elQuestion.textContent = q.question;

    var pct = (index / quizzes.length) * 100;
    if (elProgressFill) elProgressFill.style.width = pct + '%';
    if (elProgressText) elProgressText.textContent = (index + 1) + ' / ' + quizzes.length;

    elOptions.innerHTML = '';
    var options = q.quiz_type === 'fill_blank' ? null : (q.options || []);

    if (options && options.length) {
      options.forEach(function (opt) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'quiz-option';
        btn.textContent = opt;
        btn.addEventListener('click', function () { submit(q, opt, btn); });
        elOptions.appendChild(btn);
      });
    } else {
      var form = document.createElement('form');
      form.className = 'input-group';
      form.innerHTML = '<input type="text" id="js-fill-blank-input" placeholder="উত্তর লিখুন" autocomplete="off">' +
        '<button type="submit" class="btn btn--primary">Submit</button>';
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var val = document.getElementById('js-fill-blank-input').value;
        submit(q, val, null);
      });
      elOptions.appendChild(form);
    }
  }

  function submit(quiz, answer, btnEl) {
    if (answered) return;
    answered = true;

    window.IMBD.post('/quiz/submit', { quiz_id: quiz.id, answer: answer }).then(function (res) {
      if (!res.ok) {
        window.IMBD.toast(res.data.error || 'একটি সমস্যা হয়েছে।');
        answered = false;
        return;
      }
      var correct = res.data.correct;
      if (correct) correctCount++;

      Array.prototype.forEach.call(elOptions.querySelectorAll('.quiz-option'), function (el) {
        if (el.textContent === res.data.correctAnswer) el.classList.add('is-correct');
        else if (el === btnEl) el.classList.add('is-incorrect');
      });

      elResultBanner.style.display = 'block';
      elResultBanner.className = 'quiz-result-banner ' + (correct ? 'quiz-result-banner--correct' : 'quiz-result-banner--incorrect');
      elResultBanner.textContent = correct ? 'সঠিক উত্তর! ✅' : ('ভুল উত্তর। সঠিক উত্তর: ' + res.data.correctAnswer);

      if (res.data.justCompleted) {
        window.IMBD.toast('আজকের লক্ষ্য পূর্ণ হয়েছে! 🎉');
        if (res.data.unlockedWord) window.IMBD.showUnlockModal(res.data.unlockedWord);
      }

      elNextBtn.style.display = 'inline-flex';
    });
  }

  if (elNextBtn) {
    elNextBtn.addEventListener('click', function () {
      index++;
      render();
    });
  }

  render();
})();
