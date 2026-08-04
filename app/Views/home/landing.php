<section class="hero">
  <div class="container hero__inner">
    <div>
      <span class="badge">🇧🇩 বাংলাদেশের প্রথম Daily-Goal IELTS Vocabulary App</span>
      <h1>প্রতিদিন মাত্র কয়েক মিনিটে IELTS Vocabulary আয়ত্ত করুন</h1>
      <p>
        এলোমেলো শব্দ মুখস্থ করে ভুলে যাওয়ার দিন শেষ। শব্দ সোপান আপনাকে প্রতিদিন একটি নির্দিষ্ট
        লক্ষ্য দেয়, Spaced Repetition দিয়ে শব্দ মনে রাখতে সাহায্য করে, আর প্রতিটি লক্ষ্য পূরণে
        আনলক করে একটি Exclusive Band-স্কোরিং শব্দ। Band 6, 7, 8, 9 — আপনার Target Band অনুযায়ী
        সাজানো শব্দভাণ্ডার নিয়ে আজই শুরু করুন।
      </p>
      <div class="hero__badges">
        <span class="badge">🔥 Daily Streak</span>
        <span class="badge">🧠 SM-2 Spaced Repetition</span>
        <span class="badge">🎯 Band-Tagged Words</span>
        <span class="badge">📝 Quiz &amp; Practice</span>
      </div>
      <div class="mt-28">
        <a href="/subscribe" class="btn btn--accent btn--lg">🚀 এখনই শুরু করুন</a>
      </div>
    </div>

    <div class="progress-ring-demo">
      <div class="progress-ring progress-ring--pulse" data-target="5" data-achieved="3" id="js-demo-ring">
        <svg width="140" height="140" viewBox="0 0 140 140">
          <circle class="track" cx="70" cy="70" r="60"></circle>
          <circle class="fill" cx="70" cy="70" r="60"></circle>
        </svg>
        <div class="progress-ring__center">
          <span class="progress-ring__flame">🔥</span>
          <span class="progress-ring__count">3/5</span>
          <span class="progress-ring__label">আজকের লক্ষ্য</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section container">
  <div class="feature-list">
    <div class="feature-item">
      <div class="feature-item__icon">🎯</div>
      <div>
        <h3>Daily Goal + Learning Session</h3>
        <p>প্রতিদিন নির্দিষ্ট সংখ্যক শব্দ শেখার লক্ষ্য — ধারাবাহিকতা তৈরি করে।</p>
      </div>
    </div>
    <div class="feature-item">
      <div class="feature-item__icon">🧠</div>
      <div>
        <h3>Spaced Repetition (SM-2)</h3>
        <p>ঠিক সময়ে Review Queue-তে শব্দ ফিরিয়ে আনে, যাতে ভুলে না যান।</p>
      </div>
    </div>
    <div class="feature-item">
      <div class="feature-item__icon">🏆</div>
      <div>
        <h3>Exclusive Word Reward</h3>
        <p>প্রতিদিন লক্ষ্য পূরণে আনলক হয় একটি বিশেষ Band-স্কোরিং শব্দ।</p>
      </div>
    </div>
    <div class="feature-item">
      <div class="feature-item__icon">📊</div>
      <div>
        <h3>Progress Dashboard</h3>
        <p>সাপ্তাহিক অগ্রগতি, Retention %, Weak-Word List — সব একনজরে।</p>
      </div>
    </div>
  </div>
</section>

<?php if ($wordOfTheDay): ?>
<section class="section section--tight container">
  <div class="grid grid--2">
    <div class="card word-of-day-card">
      <span class="band-tag">Word of the Day · Band <?= (int) $wordOfTheDay['ielts_band_level'] ?></span>
      <div class="headword"><?= e($wordOfTheDay['headword']) ?></div>
      <?php if ($wordOfTheDay['ipa']): ?><div class="ipa"><?= e($wordOfTheDay['ipa']) ?></div><?php endif; ?>
      <p><?= e($wordOfTheDay['definition_bn'] ?: $wordOfTheDay['definition_en']) ?></p>
      <?php if ($wordOfTheDay['example_sentence']): ?>
        <p class="text-muted lang-en">"<?= e($wordOfTheDay['example_sentence']) ?>"</p>
      <?php endif; ?>
    </div>

    <?php if ($sampleQuiz): ?>
    <div class="card">
      <span class="band-tag">Sample Quiz</span>
      <p class="quiz-question mt-10"><?= e($sampleQuiz['question']) ?></p>
      <div class="quiz-options">
        <?php foreach (($sampleQuiz['options'] ?? []) as $opt): ?>
          <div class="quiz-option"><?= e($opt) ?></div>
        <?php endforeach; ?>
      </div>
      <p class="field-hint mt-12">পুরো Quiz Access পেতে Subscribe করুন।</p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="section container text-center">
  <div class="cta-block">
    <h2>🚀 এখনই Start করুন — মাত্র ৳2.78/day</h2>
    <p class="meta">Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>
    <div class="mt-20">
      <a href="/subscribe" class="btn btn--primary btn--lg">Subscribe Now</a>
    </div>
  </div>
</section>

<section class="section container">
  <div class="subscribe-box" id="quick-subscribe">
    <h2>আপনার Robi বা Airtel Number দিন</h2>
    <p>Instant Access পাবেন সব IELTS Content-এ!</p>

    <form action="/subscribe/otp/send" method="post" class="stack max-w-420 mt-20">
      <?= csrf_field($csrfToken) ?>
      <div class="field">
        <label for="landing-mobile">Mobile Number</label>
        <input type="tel" id="landing-mobile" name="mobile_number" placeholder="01XXXXXXXXX" inputmode="numeric" maxlength="11" required>
        <span class="field-hint">শুধু Robi (016/019) ও Airtel (017) Number</span>
      </div>
      <div class="lightning">⚡</div>
      <p class="text-center fs-sm">Daily মাত্র ৳2.78 — যেকোনো সময় Unsubscribe করুন</p>
      <button type="submit" class="btn btn--accent btn--block btn--lg">OTP পাঠান →</button>
    </form>
  </div>
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
