<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/** user_word_progress — the SM-2 state per user per word. */
final class ProgressRepo
{
    public function find(int $userId, int $wordId): ?array
    {
        return Db::first('SELECT * FROM user_word_progress WHERE user_id = ? AND word_id = ?', [$userId, $wordId]);
    }

    public function findById(int $id, int $userId): ?array
    {
        return Db::first('SELECT * FROM user_word_progress WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public function upsertLearned(int $userId, int $wordId): array
    {
        $existing = $this->find($userId, $wordId);
        if ($existing !== null) {
            return $existing;
        }

        Db::insert(
            'INSERT INTO user_word_progress (user_id, word_id, status, next_review_date, last_reviewed_at)
             VALUES (?, ?, "learning", DATE_ADD(CURDATE(), INTERVAL 1 DAY), NOW())',
            [$userId, $wordId]
        );

        return (array) $this->find($userId, $wordId);
    }

    public function updateSrs(int $id, array $fields): void
    {
        Db::exec(
            'UPDATE user_word_progress
                SET status = ?, ease_factor = ?, interval_days = ?, repetitions = ?,
                    next_review_date = ?, last_reviewed_at = NOW()
              WHERE id = ?',
            [
                $fields['status'], $fields['ease_factor'], $fields['interval_days'],
                $fields['repetitions'], $fields['next_review_date'], $id,
            ]
        );
    }

    public function dueForReview(int $userId, int $limit = 20): array
    {
        return Db::all(
            'SELECT p.*, w.headword, w.definition_en, w.definition_bn, w.example_sentence, w.ipa
               FROM user_word_progress p JOIN words w ON w.id = p.word_id
              WHERE p.user_id = ? AND p.next_review_date <= CURDATE() AND p.status != "mastered"
              ORDER BY p.next_review_date ASC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public function countByStatus(int $userId): array
    {
        $rows = Db::all('SELECT status, COUNT(*) AS c FROM user_word_progress WHERE user_id = ? GROUP BY status', [$userId]);
        $out  = ['new' => 0, 'learning' => 0, 'review' => 0, 'mastered' => 0];
        foreach ($rows as $row) {
            $out[(string) $row['status']] = (int) $row['c'];
        }
        return $out;
    }

    public function weakWords(int $userId, int $limit = 10): array
    {
        return Db::all(
            'SELECT p.*, w.headword, w.definition_bn FROM user_word_progress p
             JOIN words w ON w.id = p.word_id
             WHERE p.user_id = ? AND p.ease_factor < 2.3
             ORDER BY p.ease_factor ASC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    /** Days with at least one review in the last N days — feeds the dashboard bar chart and the monthly calendar. */
    public function activityByDay(int $userId, int $days = 30): array
    {
        return Db::all(
            'SELECT DATE(last_reviewed_at) AS d, COUNT(*) AS c FROM user_word_progress
              WHERE user_id = ? AND last_reviewed_at >= (CURDATE() - INTERVAL ? DAY)
              GROUP BY DATE(last_reviewed_at) ORDER BY d ASC',
            [$userId, $days]
        );
    }
}
