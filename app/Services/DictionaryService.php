<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Word;

final class DictionaryService
{
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }
        return Word::search($query);
    }

    public function show(string $headword): ?array
    {
        return Word::findByHeadword($headword);
    }
}
