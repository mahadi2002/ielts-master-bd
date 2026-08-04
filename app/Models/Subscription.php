<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Subscription
{
    public static function activeFor(string $userId): ?array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM subscriptions WHERE user_id = :u AND status = 'active' ORDER BY started_at DESC LIMIT 1"
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $userId, string $gateway, ?string $externalRef, float $dailyAmount): array
    {
        $id = Db::uuid();
        $stmt = Db::pdo()->prepare(
            "INSERT INTO subscriptions (id, user_id, gateway, external_ref, status, daily_amount, started_at, last_charged_at, next_charge_at)
             VALUES (:id, :u, :gateway, :ref, 'active', :amount, NOW(), NOW(), :next)"
        );
        $stmt->execute([
            'id' => $id,
            'u' => $userId,
            'gateway' => $gateway,
            'ref' => $externalRef,
            'amount' => $dailyAmount,
            'next' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ]);
        return self::find($id);
    }

    public static function find(string $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM subscriptions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function logEvent(string $subscriptionId, string $eventType, array $payload = []): void
    {
        $stmt = Db::pdo()->prepare(
            'INSERT INTO subscription_events (id, subscription_id, event_type, raw_payload, created_at)
             VALUES (:id, :sub, :type, :payload, NOW())'
        );
        $stmt->execute([
            'id' => Db::uuid(),
            'sub' => $subscriptionId,
            'type' => $eventType,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function expire(string $id): void
    {
        $stmt = Db::pdo()->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function dueForChargeCheck(): array
    {
        $stmt = Db::pdo()->query(
            "SELECT * FROM subscriptions WHERE status = 'active' AND next_charge_at <= NOW()"
        );
        return $stmt->fetchAll();
    }
}
