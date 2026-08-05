<?php
/** Minimal shell — deliberately not the admin layout, since there's no session yet. */
?>
<!doctype html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — <?= e($appName) ?></title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<main class="section" id="main">
  <div class="wrap max-w-420 mi-auto">
    <?= \App\Core\View::partial('partials/flash', ['notice' => $notice ?? null]) ?>

    <div class="otp-box">
      <h2>Admin Login</h2>
      <form method="post" action="/admin/login" data-guard class="stack mt-16">
        <?= csrf_field() ?>
        <div class="field">
          <label for="a-email">Email</label>
          <input class="input" type="email" id="a-email" name="email" required autofocus value="<?= e(old('email')) ?>">
        </div>
        <div class="field">
          <label for="a-pass">Password</label>
          <input class="input" type="password" id="a-pass" name="password" required>
        </div>
        <button class="btn btn--accent btn--block" type="submit">Login</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
