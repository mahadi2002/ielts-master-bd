<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class ContactRepo
{
    public function create(string $name, string $contact, string $message, ?string $ipHash): int
    {
        return Db::insert(
            'INSERT INTO contact_messages (name, contact, message, ip_hash) VALUES (?, ?, ?, ?)',
            [$name, $contact, $message, $ipHash]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function queue(string $status): array
    {
        if ($status === 'all') {
            return Db::all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 200');
        }

        return Db::all(
            'SELECT * FROM contact_messages WHERE status = ? ORDER BY created_at DESC LIMIT 200',
            [$status]
        );
    }

    public function find(int $id): ?array
    {
        return Db::first('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    }

    public function markRead(int $id): void
    {
        Db::exec('UPDATE contact_messages SET status = "read" WHERE id = ? AND status = "new"', [$id]);
    }

    public function resolve(int $id, int $adminId): void
    {
        Db::exec(
            'UPDATE contact_messages SET status = "resolved", resolved_by = ?, resolved_at = NOW() WHERE id = ?',
            [$adminId, $id]
        );
    }

    public function newCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM contact_messages WHERE status = "new"');
    }
}
