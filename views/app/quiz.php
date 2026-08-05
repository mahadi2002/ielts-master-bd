<?php /** @var array $quizzes */ ?>
<?php $this->layout('layouts/app', ['scripts' => ['quiz.js']]); ?>
<h1 class="section-title">Quiz</h1>
<p class="section-sub">MCQ ও Fill-in-the-blank দিয়ে নিজের জ্ঞান যাচাই করুন — সঠিক উত্তর আজকের লক্ষ্যেও যোগ হবে।</p>

<?php if (empty($quizzes)): ?>
  <div class="empty-state">
    <div class="empty-state__icon">📝</div>
    <p>এখনো Quiz-এর জন্য যথেষ্ট শব্দ শেখা হয়নি। প্রথমে <a href="/app/learn" class="link-primary">Learn</a>-এ যান।</p>
  </div>
<?php else: ?>
  <div class="card max-w-560 mt-24" id="js-quiz-stage" data-quizzes='<?= e(json_encode($quizzes, JSON_UNESCAPED_UNICODE)) ?>'>
    <div class="quiz-progress">
      <span id="js-quiz-progress-text"></span>
      <div class="quiz-progress__bar"><div class="quiz-progress__fill" id="js-quiz-progress-fill"></div></div>
    </div>
    <p class="quiz-question" id="js-quiz-question"></p>
    <div class="quiz-options" id="js-quiz-options"></div>
    <div class="quiz-result-banner is-hidden" id="js-quiz-result-banner"></div>
    <button class="btn btn--primary is-hidden mt-16" id="js-quiz-next">পরবর্তী প্রশ্ন →</button>
  </div>

  <div class="empty-state is-hidden" id="js-quiz-done">
    <div class="empty-state__icon">🏁</div>
    <h2>Quiz শেষ!</h2>
    <p>আপনার স্কোর: <strong id="js-quiz-score" class="lang-en"></strong></p>
    <div class="d-actions mt-16">
      <a href="/app/quiz" class="btn btn--primary">আবার Quiz দিন</a>
      <a href="/app" class="btn btn--ghost">Dashboard-এ যান</a>
    </div>
  </div>
<?php endif; ?>
