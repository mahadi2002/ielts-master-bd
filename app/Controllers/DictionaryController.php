<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\DictionaryService;

final class DictionaryController
{
    private DictionaryService $service;

    public function __construct()
    {
        $this->service = new DictionaryService();
    }

    public function search(Request $request): void
    {
        $query = trim((string) $request->query('q', ''));
        $results = $query !== '' ? $this->service->search($query) : [];

        Response::html(View::render('dictionary/search', [
            'query' => $query,
            'results' => $results,
            'rateLimited' => false,
        ]));
    }

    public function show(Request $request): void
    {
        $headword = (string) $request->param('word');
        $word = $this->service->show($headword);

        if ($word === null) {
            Response::notFound();
            return;
        }

        if ($word['synonyms']) {
            $word['synonyms'] = json_decode($word['synonyms'], true);
        }

        Response::html(View::render('dictionary/word', ['word' => $word]));
    }
}
