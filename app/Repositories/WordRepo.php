<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class WordRepo
{
    public function find(int $id): ?array
    {
        return Db::first('SELECT * FROM words WHERE id = ?', [$id]);
    }

    /** Gated — may return an exclusive reward word. Caller must check the user has actually unlocked it. */
    public function findBySlug(string $slug): ?array
    {
        return Db::first('SELECT * FROM words WHERE slug = ?', [$slug]);
    }

    /** Public dictionary — never returns a reward-only word, regardless of who asks. */
    public function findPublicBySlug(string $slug): ?array
    {
        return Db::first('SELECT * FROM words WHERE slug = ? AND is_exclusive = 0', [$slug]);
    }

    public function incrementView(int $id): void
    {
        Db::exec('UPDATE words SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    /** Free-tier dictionary search — never returns exclusive reward words. */
    public function search(string $query, int $limit = 20): array
    {
        $like = $query . '%';
        try {
            return Db::all(
                'SELECT * FROM words
                  WHERE is_exclusive = 0
                    AND (headword LIKE ? OR MATCH(headword, definition_en) AGAINST (? IN NATURAL LANGUAGE MODE))
                  ORDER BY frequency_rank IS NULL, frequency_rank ASC
                  LIMIT ' . (int) $limit,
                [$like, $query]
            );
        } catch (\PDOException) {
            // FULLTEXT index unavailable (see migrate.php) — LIKE-only fallback.
            return Db::all(
                'SELECT * FROM words WHERE is_exclusive = 0 AND headword LIKE ?
                  ORDER BY frequency_rank IS NULL, frequency_rank ASC LIMIT ' . (int) $limit,
                [$like]
            );
        }
    }

    /** Paginated catalog browse for subscribers, optionally filtered by band. */
    public function browse(?int $band, int $limit, int $offset): array
    {
        if ($band !== null) {
            return Db::all(
                'SELECT * FROM words WHERE is_exclusive = 0 AND ielts_band_level = ?
                  ORDER BY frequency_rank IS NULL, frequency_rank ASC, headword
                  LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
                [$band]
            );
        }
        return Db::all(
            'SELECT * FROM words WHERE is_exclusive = 0
              ORDER BY frequency_rank IS NULL, frequency_rank ASC, headword
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
        );
    }

    public function countBrowse(?int $band): int
    {
        if ($band !== null) {
            return (int) Db::value('SELECT COUNT(*) FROM words WHERE is_exclusive = 0 AND ielts_band_level = ?', [$band]);
        }
        return (int) Db::value('SELECT COUNT(*) FROM words WHERE is_exclusive = 0');
    }

    /** Words the user hasn't started learning yet, weighted toward their target band. */
    public function nextForLearning(int $userId, ?float $targetBand, int $limit): array
    {
        $band = $targetBand !== null ? (int) ceil($targetBand) : 7;

        return Db::all(
            'SELECT w.* FROM words w
             LEFT JOIN user_word_progress p ON p.word_id = w.id AND p.user_id = ?
             WHERE w.is_exclusive = 0 AND p.id IS NULL
             ORDER BY ABS(CAST(w.ielts_band_level AS SIGNED) - ?), w.frequency_rank IS NULL, w.frequency_rank ASC
             LIMIT ' . (int) $limit,
            [$userId, $band]
        );
    }

    public function randomExclusiveForBand(int $band, int $userId): ?array
    {
        return Db::first(
            'SELECT w.* FROM words w
             LEFT JOIN user_collection uc ON uc.word_id = w.id AND uc.user_id = ?
             WHERE w.is_exclusive = 1 AND w.ielts_band_level = ? AND uc.word_id IS NULL
             ORDER BY RAND() LIMIT 1',
            [$userId, $band]
        );
    }

    public function countUnclaimedExclusive(int $band): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM words w
              WHERE w.is_exclusive = 1 AND w.ielts_band_level = ?
                AND w.id NOT IN (SELECT word_id FROM user_collection)',
            [$band]
        );
    }

    public function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO words (slug, headword, ipa, part_of_speech, definition_en, definition_bn,
                example_sentence, synonyms, ielts_band_level, task_tag, is_exclusive, frequency_rank, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['slug'], $data['headword'], $data['ipa'] ?? null, $data['part_of_speech'] ?? null,
                $data['definition_en'], $data['definition_bn'] ?? null, $data['example_sentence'] ?? null,
                isset($data['synonyms']) ? json_encode($data['synonyms'], JSON_UNESCAPED_UNICODE) : null,
                $data['ielts_band_level'], $data['task_tag'] ?? null,
                !empty($data['is_exclusive']) ? 1 : 0, $data['frequency_rank'] ?? null,
            ]
        );
    }

    public function update(int $id, array $data): void
    {
        Db::exec(
            'UPDATE words SET slug = ?, headword = ?, ipa = ?, part_of_speech = ?, definition_en = ?,
                definition_bn = ?, example_sentence = ?, synonyms = ?, ielts_band_level = ?, task_tag = ?,
                is_exclusive = ?, frequency_rank = ?
             WHERE id = ?',
            [
                $data['slug'], $data['headword'], $data['ipa'] ?? null, $data['part_of_speech'] ?? null,
                $data['definition_en'], $data['definition_bn'] ?? null, $data['example_sentence'] ?? null,
                isset($data['synonyms']) ? json_encode($data['synonyms'], JSON_UNESCAPED_UNICODE) : null,
                $data['ielts_band_level'], $data['task_tag'] ?? null,
                !empty($data['is_exclusive']) ? 1 : 0, $data['frequency_rank'] ?? null,
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        Db::exec('DELETE FROM words WHERE id = ?', [$id]);
    }

    public function paginate(int $limit, int $offset): array
    {
        return Db::all('SELECT * FROM words ORDER BY created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset);
    }

    public function countAll(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM words');
    }
}
