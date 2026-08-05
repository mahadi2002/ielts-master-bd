<footer class="site-footer">
  <div class="wrap footer__grid">
    <div class="footer__links">
      <a href="/privacy">Privacy Policy</a>
      <a href="/terms">Terms &amp; Conditions</a>
      <a href="/contact">Contact Us</a>
    </div>
    <div class="footer__operators">Robi &amp; Airtel Bangladesh</div>
    <div class="footer__copyright">© <?= e(date('Y')) ?> IELTS Master BD — সর্বস্বত্ব সংরক্ষিত</div>
    <p class="footer__disclaimer">
      ⚠️ Daily মাত্র ৳<?= e($dailyAmount ?? '2.78') ?> সরাসরি আপনার Robi/Airtel Account থেকে কাটা হবে।
      Unsubscribe করতে <?= e($config_stop ?? 'STOP') ?> লিখে <?= e($shortcode ?? '16216') ?> নম্বরে SMS করুন, অথবা Account পাতা থেকে যেকোনো সময় বন্ধ করুন।
    </p>
  </div>
</footer>
