<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Word
{
    public static function find(string $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM words WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByHeadword(string $headword): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM words WHERE headword = :h LIMIT 1');
        $stmt->execute(['h' => $headword]);
        return $stmt->fetch() ?: null;
    }

    public static function search(string $query, int $limit = 20): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM words
             WHERE is_exclusive = FALSE AND (headword LIKE :q OR MATCH(headword, definition_en) AGAINST (:q2 IN NATURAL LANGUAGE MODE))
             ORDER BY frequency_rank IS NULL, frequency_rank ASC LIMIT :limit"
        );
        $stmt->bindValue('q', $query . '%');
        $stmt->bindValue('q2', $query);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Words the user hasn't started learning yet, for today's session, tagged toward their target band. */
    public static function nextForLearning(string $userId, ?float $targetBand, int $limit): array
    {
        $band = $targetBand ? (int) ceil($targetBand) : 7;
        $stmt = Db::pdo()->prepare(
            "SELECT w.* FROM words w
             LEFT JOIN user_word_progress p ON p.word_id = w.id AND p.user_id = :uid
             WHERE w.is_exclusive = FALSE AND p.id IS NULL
             ORDER BY ABS(CAST(w.ielts_band_level AS SIGNED) - :band), w.frequency_rank IS NULL, w.frequency_rank ASC
             LIMIT :limit"
        );
        $stmt->bindValue('uid', $userId);
        $stmt->bindValue('band', $band, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function randomExclusiveForBand(int $band, string $userId): ?array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT w.* FROM words w
             LEFT JOIN user_collection uc ON uc.word_id = w.id AND uc.user_id = :uid
             WHERE w.is_exclusive = TRUE AND w.ielts_band_level = :band AND uc.word_id IS NULL
             ORDER BY RAND() LIMIT 1"
        );
        $stmt->execute(['uid' => $userId, 'band' => $band]);
        return $stmt->fetch() ?: null;
    }

    public static function countUnclaimedExclusive(int $band): int
    {
        $stmt = Db::pdo()->prepare(
            "SELECT COUNT(*) c FROM words w
             WHERE w.is_exclusive = TRUE AND w.ielts_band_level = :band
             AND w.id NOT IN (SELECT word_id FROM user_collection)"
        );
        $stmt->execute(['band' => $band]);
        return (int) $stmt->fetch()['c'];
    }

    public static function create(array $data): string
    {
        $id = Db::uuid();
        $stmt = Db::pdo()->prepare(
            'INSERT INTO words (id, headword, ipa, part_of_speech, definition_en, definition_bn,
                example_sentence, synonyms, ielts_band_level, task_tag, is_exclusive, frequency_rank, created_at)
             VALUES (:id, :headword, :ipa, :pos, :def_en, :def_bn, :example, :synonyms, :band, :task_tag, :exclusive, :freq, NOW())'
        );
        $stmt->execute([
            'id' => $id,
            'headword' => $data['headword'],
            'ipa' => $data['ipa'] ?? null,
            'pos' => $data['part_of_speech'] ?? null,
            'def_en' => $data['definition_en'],
            'def_bn' => $data['definition_bn'] ?? null,
            'example' => $data['example_sentence'] ?? null,
            'synonyms' => isset($data['synonyms']) ? json_encode($data['synonyms'], JSON_UNESCAPED_UNICODE) : null,
            'band' => $data['ielts_band_level'],
            'task_tag' => $data['task_tag'] ?? null,
            'exclusive' => !empty($data['is_exclusive']) ? 1 : 0,
            'freq' => $data['frequency_rank'] ?? null,
        ]);
        return $id;
    }

    public static function delete(string $id): void
    {
        $stmt = Db::pdo()->prepare('DELETE FROM words WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function paginate(int $limit = 50, int $offset = 0): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM words ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        return (int) Db::pdo()->query('SELECT COUNT(*) c FROM words')->fetch()['c'];
    }
}
