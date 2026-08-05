<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/** qa_questions — the moderated ask-a-question UGC loop. */
final class QuestionRepo
{
    public function find(int $id): ?array
    {
        // No join to whoever answered — answers are attributed to "the team"
        // in the UI, not a specific staff phone number.
        return Db::first(
            'SELECT q.*, w.headword, w.slug AS word_slug
               FROM qa_questions q
               LEFT JOIN words w ON w.id = q.word_id
              WHERE q.id = ?',
            [$id]
        );
    }

    public function create(int $userId, ?int $wordId, string $title, string $body): int
    {
        return Db::insert(
            'INSERT INTO qa_questions (user_id, word_id, title, body, status, created_at)
             VALUES (?, ?, ?, ?, "open", NOW())',
            [$userId, $wordId, $title, $body]
        );
    }

    public function answer(int $id, int $adminId, string $answer): void
    {
        Db::exec(
            'UPDATE qa_questions SET answer = ?, status = "answered", answered_by = ?, answered_at = NOW() WHERE id = ?',
            [$answer, $adminId, $id]
        );
    }

    /** Answered questions, newest first — the public/gated feed. */
    public function answered(int $limit = 50, int $offset = 0): array
    {
        return Db::all(
            'SELECT q.*, w.headword, w.slug AS word_slug FROM qa_questions q
             LEFT JOIN words w ON w.id = q.word_id
             WHERE q.status = "answered"
             ORDER BY q.answered_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
        );
    }

    public function forUser(int $userId, int $limit = 50): array
    {
        return Db::all(
            'SELECT q.*, w.headword, w.slug AS word_slug FROM qa_questions q
             LEFT JOIN words w ON w.id = q.word_id
             WHERE q.user_id = ? ORDER BY q.created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public function open(int $limit = 50): array
    {
        return Db::all(
            'SELECT q.*, u.msisdn_last4 FROM qa_questions q JOIN users u ON u.id = q.user_id
             WHERE q.status = "open" ORDER BY q.created_at ASC LIMIT ' . (int) $limit
        );
    }

    public function countOpen(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM qa_questions WHERE status = "open"');
    }
}
