<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>IELTS Master BD | শব্দ সোপান</title>
<meta name="description" content="প্রতিদিন IELTS Vocabulary শিখুন, Spaced Repetition দিয়ে মনে রাখুন, Exclusive Word Reward জিতুন।">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="<?= $auth ? 'is-authed' : 'is-guest' ?>">

<header class="site-header">
  <div class="container site-header__inner">
    <a href="/" class="brand">
      <span class="brand__mark" aria-hidden="true">শ</span>
      <span class="brand__text">IELTS Master BD<small>শব্দ সোপান</small></span>
    </a>

    <nav class="main-nav" aria-label="প্রধান মেনু">
      <a href="/dictionary">Dictionary</a>
      <?php if ($auth): ?>
        <a href="/learn">Learn</a>
        <a href="/review">Review</a>
        <a href="/quiz">Quiz</a>
        <a href="/dashboard">Dashboard</a>
        <a href="/collection">Collection</a>
      <?php endif; ?>
    </nav>

    <div class="header-cta">
      <?php if ($auth): ?>
        <span class="streak-chip" title="Current streak">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2s5 5.5 5 10a5 5 0 1 1-10 0c0-1.2.5-2.2 1-3 .2 1 1 1.5 1.5 1 .8-2 .5-4 .5-5C10 3.5 12 2 12 2z" fill="#E8A94A"/></svg>
          <span id="js-streak-count"><?= (int) ($streakCount ?? 0) ?></span>
        </span>
        <a href="/logout" class="btn btn--ghost btn--sm">Logout</a>
      <?php else: ?>
        <a href="/subscribe" class="btn btn--accent btn--sm">মাত্র ৳2.78/day</a>
      <?php endif; ?>
    </div>

    <button class="nav-toggle" id="js-nav-toggle" aria-label="মেনু খুলুন" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<?php if ($flash): ?>
  <div class="flash flash--<?= e($flashType) ?>" role="status">
    <div class="container"><?= e($flash) ?></div>
  </div>
<?php endif; ?>

<main class="site-main">
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="container footer__grid">
    <div class="footer__links">
      <a href="/privacy-policy">Privacy Policy</a>
      <a href="/terms">Terms &amp; Conditions</a>
      <a href="/contact">Contact Us</a>
      <span class="footer__powered">Powered by BDApps</span>
    </div>
    <div class="footer__operators">Robi &amp; Airtel Bangladesh</div>
    <div class="footer__copyright">© 2026 IELTS Master BD — সর্বস্বত্ব সংরক্ষিত</div>
    <p class="footer__disclaimer">
      ⚠️ এই Service BDApps-এর মাধ্যমে Charge করা হয়। Daily ৳2.78 আপনার Robi /Airtel Account থেকে কাটা হবে।
      Unsubscribe করতে STOP লিখে 16216 নম্বরে SMS করুন।
    </p>
  </div>
</footer>

<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraScripts)): foreach ($extraScripts as $script): ?>
  <script src="<?= asset($script) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
