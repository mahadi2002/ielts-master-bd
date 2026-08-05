<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\WordRepo;

final class DictionaryService
{
    public function __construct(private WordRepo $words = new WordRepo())
    {
    }

    public function search(string $query): array
    {
        $query = trim($query);
        return $query === '' ? [] : $this->words->search($query);
    }

    public function show(string $slug): ?array
    {
        return $this->words->findPublicBySlug($slug);
    }
}
