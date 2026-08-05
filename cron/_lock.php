<?php
declare(strict_types=1);

/**
 * flock-based single-instance guard, shared by every cron script.
 * Usage: $release = cron_lock('charge_cycle'); if (!$release) exit(0);
 */
function cron_lock(string $name): ?\Closure
{
    $dir = APP_ROOT . '/storage/cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }

    $path   = $dir . '/' . $name . '.lock';
    $handle = fopen($path, 'c');

    if ($handle === false || !flock($handle, LOCK_EX | LOCK_NB)) {
        if (is_resource($handle)) {
            fclose($handle);
        }
        fwrite(STDERR, "[$name] already running, exiting.\n");
        return null;
    }

    return static function () use ($handle): void {
        flock($handle, LOCK_UN);
        fclose($handle);
    };
}
