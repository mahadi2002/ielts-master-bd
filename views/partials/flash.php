<?php
/** @var array|null $notice */
if (empty($notice)) {
    return;
}

$class = match ((string) $notice['type']) {
    'success' => 'notice--success',
    'error'   => 'notice--error',
    'warn'    => 'notice--warn',
    default   => 'notice--info',
};

$icon = match ((string) $notice['type']) {
    'success' => '✓',
    'error'   => '!',
    'warn'    => '⚠',
    default   => 'i',
};
?>
<div class="notice <?= e($class) ?>" role="status" data-flash="<?= e((string) $notice['type']) ?>">
  <span class="notice__icon" aria-hidden="true"><?= e($icon) ?></span>
  <span><?= e((string) $notice['text']) ?></span>
</div>
