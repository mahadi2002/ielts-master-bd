<?php
/**
 * @var array  $users
 * @var string $last4
 */
$this->layout('layouts/admin', ['title' => 'Users']);
?>
<h1 class="section-title">Users</h1>

<form method="get" action="/admin/users" class="search-bar mt-16">
  <input class="input" type="text" name="last4" placeholder="শেষ ৪ সংখ্যা দিয়ে খুঁজুন" maxlength="4" value="<?= e($last4) ?>">
  <button type="submit" class="btn btn--primary">খুঁজুন</button>
</form>

<table class="data-table mt-20">
  <thead><tr><th>Number</th><th>Operator</th><th>Status</th><th>Sub Status</th><th>যোগদান</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td class="lang-en"><a href="/admin/users/<?= (int) $u['id'] ?>" class="link-primary">****<?= e($u['msisdn_last4']) ?></a></td>
        <td><?= e(ucfirst($u['operator'])) ?></td>
        <td><?= e($u['status']) ?></td>
        <td><?= e((string) ($u['sub_status'] ?? '—')) ?></td>
        <td><?= bn_date($u['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($users)): ?><tr><td colspan="5" class="text-center text-muted">কোনো User পাওয়া যায়নি।</td></tr><?php endif; ?>
  </tbody>
</table>
