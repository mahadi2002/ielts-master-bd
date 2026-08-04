<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class UserWordProgress
{
    public static function find(string $userId, string $wordId): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM user_word_progress WHERE user_id = :u AND word_id = :w');
        $stmt->execute(['u' => $userId, 'w' => $wordId]);
        return $stmt->fetch() ?: null;
    }

    public static function upsertLearned(string $userId, string $wordId): array
    {
        $existing = self::find($userId, $wordId);
        if ($existing) {
            return $existing;
        }
        $id = Db::uuid();
        $stmt = Db::pdo()->prepare(
            'INSERT INTO user_word_progress (id, user_id, word_id, status, next_review_date, last_reviewed_at)
             VALUES (:id, :u, :w, :status, :next, NOW())'
        );
        $stmt->execute([
            'id' => $id,
            'u' => $userId,
            'w' => $wordId,
            'status' => 'learning',
            'next' => date('Y-m-d', strtotime('+1 day')),
        ]);
        return self::find($userId, $wordId);
    }

    public static function updateSrs(string $id, array $fields): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE user_word_progress
             SET status = :status, ease_factor = :ease, interval_days = :interval,
                 repetitions = :reps, next_review_date = :next, last_reviewed_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $fields['status'],
            'ease' => $fields['ease_factor'],
            'interval' => $fields['interval_days'],
            'reps' => $fields['repetitions'],
            'next' => $fields['next_review_date'],
            'id' => $id,
        ]);
    }

    public static function dueForReview(string $userId, int $limit = 20): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT p.*, w.headword, w.definition_en, w.definition_bn, w.example_sentence, w.ipa
             FROM user_word_progress p JOIN words w ON w.id = p.word_id
             WHERE p.user_id = :u AND p.next_review_date <= CURDATE() AND p.status != :mastered
             ORDER BY p.next_review_date ASC LIMIT :limit'
        );
        $stmt->bindValue('u', $userId);
        $stmt->bindValue('mastered', 'mastered');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $userId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT status, COUNT(*) c FROM user_word_progress WHERE user_id = :u GROUP BY status'
        );
        $stmt->execute(['u' => $userId]);
        $result = ['new' => 0, 'learning' => 0, 'review' => 0, 'mastered' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['c'];
        }
        return $result;
    }

    public static function weakWords(string $userId, int $limit = 10): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT p.*, w.headword, w.definition_bn FROM user_word_progress p
             JOIN words w ON w.id = p.word_id
             WHERE p.user_id = :u AND p.ease_factor < 2.3
             ORDER BY p.ease_factor ASC LIMIT :limit'
        );
        $stmt->bindValue('u', $userId);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function learnedInLastDays(string $userId, int $days = 7): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT DATE(last_reviewed_at) d, COUNT(*) c FROM user_word_progress
             WHERE user_id = :u AND last_reviewed_at >= (CURDATE() - INTERVAL :days DAY)
             GROUP BY DATE(last_reviewed_at) ORDER BY d ASC"
        );
        $stmt->bindValue('u', $userId);
        $stmt->bindValue('days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
