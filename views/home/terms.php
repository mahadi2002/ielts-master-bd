<?php $this->layout('layouts/public', ['title' => 'Terms & Conditions']); ?>
<section class="section">
  <div class="wrap prose max-w-640 mi-auto">
    <h1>Terms &amp; Conditions</h1>

    <h3>Subscription &amp; Billing</h3>
    <p>IELTS Master BD একটি Daily Subscription Service — মাত্র ৳<?= e($dailyAmount ?? '2.78') ?>/day, সরাসরি
      আপনার Robi/Airtel Balance থেকে কাটা হয়। বর্তমানে শুধু Robi ও Airtel Number সাপোর্টেড।</p>

    <h3>Unsubscribe</h3>
    <p>যেকোনো সময় Account পাতা থেকে Unsubscribe করতে পারবেন, অথবা <?= e($shortcode ?? '16216') ?> নম্বরে
      <strong>STOP</strong> লিখে SMS করে বন্ধ করতে পারবেন। Unsubscribe করার পর আর কোনো Charge কাটা হবে না।</p>

    <h3>Content ব্যবহার</h3>
    <p>এই App-এর Vocabulary, Guide, ও Quiz Content শুধুমাত্র ব্যক্তিগত শেখার উদ্দেশ্যে। পুনঃবিতরণ বা
      বাণিজ্যিক ব্যবহারের অনুমতি নেই।</p>

    <h3>দায়বদ্ধতা</h3>
    <p>এই App IELTS পরীক্ষার প্রস্তুতিতে সহায়তা করে, কিন্তু কোনো নির্দিষ্ট Band Score-এর গ্যারান্টি দেয় না।</p>
  </div>
</section>
