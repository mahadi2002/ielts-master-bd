<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class GuideRepo
{
    /** Everything except body_md — the gated column never reaches a non-subscriber's query. */
    private const TEASER_COLUMNS = 'id, slug, title, category, excerpt, band_relevance, view_count, published_at, created_at';

    public function find(int $id): ?array
    {
        return Db::first('SELECT * FROM guides WHERE id = ?', [$id]);
    }

    /** Gated — includes body_md. Only ever called from an already-subscription-checked route. */
    public function findBySlug(string $slug): ?array
    {
        return Db::first('SELECT * FROM guides WHERE slug = ? AND is_published = 1', [$slug]);
    }

    /** Public — excerpt only. body_md is not in the SELECT, so there is nothing to leak. */
    public function findTeaserBySlug(string $slug): ?array
    {
        return Db::first(
            'SELECT ' . self::TEASER_COLUMNS . ' FROM guides WHERE slug = ? AND is_published = 1',
            [$slug]
        );
    }

    public function incrementView(int $id): void
    {
        Db::exec('UPDATE guides SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    /** Gated listing — full rows are fine here, the caller is already subscription-checked. */
    public function published(?string $category = null): array
    {
        if ($category !== null) {
            return Db::all(
                'SELECT * FROM guides WHERE is_published = 1 AND category = ? ORDER BY published_at DESC',
                [$category]
            );
        }
        return Db::all('SELECT * FROM guides WHERE is_published = 1 ORDER BY published_at DESC');
    }

    /** Public listing — teaser columns only. */
    public function publishedTeaser(?string $category = null): array
    {
        if ($category !== null) {
            return Db::all(
                'SELECT ' . self::TEASER_COLUMNS . ' FROM guides WHERE is_published = 1 AND category = ? ORDER BY published_at DESC',
                [$category]
            );
        }
        return Db::all('SELECT ' . self::TEASER_COLUMNS . ' FROM guides WHERE is_published = 1 ORDER BY published_at DESC');
    }

    public function create(array $data): int
    {
        return Db::insert(
            'INSERT INTO guides (slug, title, category, excerpt, body_md, band_relevance, is_published, published_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $data['slug'], $data['title'], $data['category'], $data['excerpt'], $data['body_md'],
                $data['band_relevance'] ?? null, !empty($data['is_published']) ? 1 : 0,
                !empty($data['is_published']) ? date('Y-m-d H:i:s') : null,
            ]
        );
    }

    public function update(int $id, array $data): void
    {
        Db::exec(
            'UPDATE guides SET slug = ?, title = ?, category = ?, excerpt = ?, body_md = ?, band_relevance = ?, is_published = ?
             WHERE id = ?',
            [
                $data['slug'], $data['title'], $data['category'], $data['excerpt'], $data['body_md'],
                $data['band_relevance'] ?? null, !empty($data['is_published']) ? 1 : 0, $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        Db::exec('DELETE FROM guides WHERE id = ?', [$id]);
    }

    public function paginate(int $limit, int $offset): array
    {
        return Db::all('SELECT * FROM guides ORDER BY created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset);
    }

    public function countAll(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM guides');
    }
}
