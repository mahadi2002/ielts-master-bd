<?php
/**
 * @var array $user
 * @var array $charges
 */
$this->layout('layouts/admin', ['title' => 'User #' . $user['id']]);
?>
<a href="/admin/users" class="text-muted">← সব User-এ ফিরে যান</a>

<div class="card mt-16 max-w-640">
  <h2>****<?= e($user['msisdn_last4']) ?></h2>
  <p class="text-muted">Operator: <?= e(ucfirst($user['operator'])) ?> · যোগদান: <?= bn_date($user['created_at']) ?></p>

  <form method="post" action="/admin/users/<?= (int) $user['id'] ?>" class="mt-16">
    <?= csrf_field() ?>
    <div class="field">
      <label for="status">Status</label>
      <select class="input" id="status" name="status">
        <?php foreach (['pending', 'active', 'blocked'] as $s): ?>
          <option value="<?= $s ?>"<?= $user['status'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn--primary btn--sm">Update করুন</button>
  </form>
</div>

<?php if (!empty($charges)): ?>
  <div class="card mt-16 max-w-640">
    <h3>Charge History</h3>
    <table class="data-table mt-10">
      <thead><tr><th>Amount</th><th>Status</th><th>Code</th><th>সময়</th></tr></thead>
      <tbody>
        <?php foreach ($charges as $c): ?>
          <tr>
            <td>৳<?= e($c['amount']) ?></td>
            <td><?= e($c['status']) ?></td>
            <td><?= e((string) ($c['status_code'] ?? '—')) ?></td>
            <td><?= bn_date($c['attempted_at'], true) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
