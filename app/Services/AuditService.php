<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;
use App\Core\Logger;
use Throwable;

/**
 * Append-only trail for anything that touches identity or money.
 * NEVER put a full MSISDN or an OTP in `meta` — an audit log that leaks the
 * thing it's supposed to be auditing access to isn't much of an audit log.
 */
final class AuditService
{
    public static function log(
        string $action,
        string $actorType = 'system',
        ?int $actorId = null,
        ?string $entity = null,
        ?int $entityId = null,
        array $meta = [],
        ?string $ipHash = null,
        ?string $uaHash = null,
    ): void {
        try {
            Db::exec(
                'INSERT INTO audit_log (actor_type, actor_id, action, entity, entity_id, ip_hash, ua_hash, meta)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $actorType,
                    $actorId,
                    $action,
                    $entity,
                    $entityId,
                    $ipHash,
                    $uaHash,
                    $meta === [] ? null : (string) json_encode(self::scrub($meta), JSON_UNESCAPED_UNICODE),
                ]
            );
        } catch (Throwable $e) {
            // Auditing must never break the request it is auditing.
            Logger::error('audit.write_failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }

    private static function scrub(array $meta): array
    {
        foreach (['msisdn', 'phone', 'otp', 'code', 'password'] as $key) {
            unset($meta[$key]);
        }
        return $meta;
    }
}
