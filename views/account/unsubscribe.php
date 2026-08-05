<?php $this->layout('layouts/public', ['title' => 'Unsubscribe']); ?>
<section class="section">
  <div class="wrap max-w-520 mi-auto text-center">
    <h1 class="section-title">Unsubscribe করতে চান?</h1>
    <p class="section-sub">Unsubscribe করলে আর কোনো Charge কাটা হবে না, এবং তাৎক্ষণিকভাবে Access বন্ধ হয়ে যাবে।
      পরে আবার যেকোনো সময় Subscribe করতে পারবেন।</p>

    <form method="post" action="/account/unsubscribe" class="stack mt-24">
      <?= csrf_field() ?>
      <div class="field">
        <label for="reason">কারণ (ঐচ্ছিক)</label>
        <textarea class="input" id="reason" name="reason" rows="3" maxlength="160"></textarea>
      </div>
      <button type="submit" class="btn btn--primary btn--block btn--lg">নিশ্চিতভাবে Unsubscribe করুন</button>
    </form>

    <p class="fs-sm mt-16"><a href="/account" class="link-primary">না, ফিরে যান</a></p>
  </div>
</section>
