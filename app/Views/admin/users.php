<section class="section container">
  <h1 class="section-title">Admin · Users</h1>
  <p class="section-sub">মোট <?= (int) $total ?> জন User।</p>

  <table class="data-table mt-20">
    <thead><tr><th>Mobile</th><th>Operator</th><th>Target Band</th><th>Role</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="lang-en"><?= e($u['mobile_number']) ?></td>
          <td><?= e($u['operator']) ?></td>
          <td><?= e((string) ($u['target_band'] ?? '—')) ?></td>
          <td><?= e($u['role']) ?></td>
          <td><?= e(date('d M, Y', strtotime($u['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
