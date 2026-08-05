<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class AdminAlertRepo
{
    public function hasUnresolved(string $type, int $band): bool
    {
        $row = Db::first(
            "SELECT id FROM admin_alerts WHERE alert_type = ? AND resolved = 0
              AND JSON_EXTRACT(context, '$.band') = ? LIMIT 1",
            [$type, $band]
        );
        return $row !== null;
    }

    public function raise(string $type, array $context): void
    {
        Db::exec(
            'INSERT INTO admin_alerts (alert_type, context, resolved, created_at) VALUES (?, ?, 0, NOW())',
            [$type, json_encode($context, JSON_UNESCAPED_UNICODE)]
        );
    }

    public function unresolved(int $limit = 20): array
    {
        return Db::all('SELECT * FROM admin_alerts WHERE resolved = 0 ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }
}
