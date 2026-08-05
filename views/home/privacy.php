<?php $this->layout('layouts/public', ['title' => 'Privacy Policy']); ?>
<section class="section">
  <div class="wrap prose max-w-640 mi-auto">
    <h1>Privacy Policy</h1>
    <p class="text-muted">সর্বশেষ আপডেট: <?= bn_date(date('Y-m-01')) ?></p>

    <h3>আমরা কী তথ্য রাখি</h3>
    <p>আপনার Mobile Number encrypted (AES-256-GCM) অবস্থায় সংরক্ষণ করা হয় এবং শুধুমাত্র
      Subscription যাচাই ও Billing-এর জন্য ব্যবহৃত হয়। Number খুঁজে বের করার জন্য একটি আলাদা,
      one-way hash ব্যবহার করা হয় — এই hash থেকে আসল Number উদ্ধার করা সম্ভব না।</p>

    <h3>কী তথ্য আমরা রাখি না</h3>
    <p>আমরা কখনো আপনার OTP Code লগ ফাইলে রাখি না, এবং কোনো পাসওয়ার্ড রাখি না — এই App-এ Login
      সম্পূর্ণভাবে OTP-based।</p>

    <h3>Account মুছে ফেলা</h3>
    <p>Account Settings থেকে যেকোনো সময় Unsubscribe বা সম্পূর্ণ Account Delete করতে পারবেন।
      Delete করলে আপনার পরিচয়সংক্রান্ত তথ্য স্থায়ীভাবে মুছে ফেলা হয়; Billing History আইনি
      প্রয়োজনে সীমিত সময়ের জন্য রাখা হতে পারে, কিন্তু তা আপনার সাথে আর যুক্ত থাকবে না।</p>

    <h3>যোগাযোগ</h3>
    <p>কোনো প্রশ্ন থাকলে <a href="/contact" class="link-primary">Contact Us</a> পাতা থেকে যোগাযোগ করুন।</p>
  </div>
</section>
