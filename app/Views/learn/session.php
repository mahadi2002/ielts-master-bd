<section class="section container">
  <?php if ($completed): ?>
    <div class="celebration">
      <div class="fs-22">✅</div>
      <h1 class="section-title">আজকের লক্ষ্য পূর্ণ ✅</h1>
      <p class="section-sub mi-auto">আজকে আরও শেখার জন্য Review Queue-তে যান অথবা Quiz দিয়ে অনুশীলন করুন।</p>
      <div class="d-actions mt-20">
        <a href="/review" class="btn btn--primary">Review Queue দেখুন</a>
        <a href="/quiz" class="btn btn--ghost">Quiz দিন</a>
      </div>
    </div>
  <?php else: ?>
    <div class="text-center mb-24">
      <div class="progress-ring progress-ring--pulse" id="js-goal-ring"
           data-target="<?= (int) $progress['goal_target'] ?>" data-achieved="<?= (int) $progress['goal_achieved'] ?>">
        <svg width="140" height="140" viewBox="0 0 140 140">
          <circle class="track" cx="70" cy="70" r="60"></circle>
          <circle class="fill" cx="70" cy="70" r="60"></circle>
        </svg>
        <div class="progress-ring__center">
          <span class="progress-ring__flame">🔥</span>
          <span class="progress-ring__count"><?= (int) $progress['goal_achieved'] ?>/<?= (int) $progress['goal_target'] ?></span>
          <span class="progress-ring__label">আজকের লক্ষ্য</span>
        </div>
      </div>
    </div>

    <div class="flashcard-stage" id="js-flashcard-stage"
         data-words='<?= e(json_encode($words, JSON_UNESCAPED_UNICODE)) ?>'
         data-goal-target="<?= (int) $progress['goal_target'] ?>"
         data-goal-achieved="<?= (int) $progress['goal_achieved'] ?>">

      <div class="flashcard-scene">
        <div class="flashcard" id="js-flashcard">
          <div class="flashcard__face flashcard__face--front">
            <span class="flashcard__pos" id="js-fc-pos"></span>
            <div class="flashcard__headword" id="js-fc-headword"></div>
            <span class="flashcard__pos" id="js-fc-ipa"></span>
            <p class="flashcard__hint">কার্ডে ক্লিক করুন অর্থ দেখতে</p>
          </div>
          <div class="flashcard__face flashcard__face--back">
            <p id="js-fc-def-en" class="lang-en"></p>
            <p id="js-fc-def-bn"></p>
            <p class="flashcard__hint lang-en" id="js-fc-example"></p>
          </div>
        </div>
      </div>

      <p class="flashcard-progress-text" id="js-fc-progress-text"></p>

      <div class="flashcard-actions">
        <button class="btn btn--ghost" id="js-fc-again">আবার দেখাও</button>
        <button class="btn btn--primary" id="js-fc-know">শিখেছি ✓</button>
      </div>
    </div>

    <div class="empty-state is-hidden" id="js-fc-done">
      <div class="empty-state__icon">🎉</div>
      <p>এই Session-এর সব শব্দ শেষ! Refresh করে আরও দেখুন অথবা Review Queue-তে যান।</p>
      <a href="/review" class="btn btn--primary">Review Queue</a>
    </div>
  <?php endif; ?>
</section>

<div class="modal-backdrop" id="js-unlock-modal">
  <div class="modal">
    <div class="fs-24">🏆</div>
    <h2>অভিনন্দন! একটি নতুন শব্দ আনলক হয়েছে</h2>
    <div class="celebration__word" data-field="headword"></div>
    <p data-field="definition_bn"></p>
    <button class="btn btn--primary" data-close-modal>ঠিক আছে</button>
  </div>
</div>

<script src="<?= asset('js/flashcard.js') ?>"></script>
