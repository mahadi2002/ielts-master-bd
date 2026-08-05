<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\DictionaryService;

final class DictionaryController extends Controller
{
    private DictionaryService $service;

    public function __construct()
    {
        $this->service = new DictionaryService();
    }

    public function index(Request $request): Response
    {
        $query   = $request->str('q');
        $results = $this->service->search($query);

        return $this->view('dictionary/index', [
            'title'   => 'Dictionary Search',
            'query'   => $query,
            'results' => $results,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $word = $this->service->show($slug);

        if ($word === null) {
            $this->notFound();
        }

        if ($word['synonyms']) {
            $word['synonyms'] = json_decode((string) $word['synonyms'], true);
        }

        return $this->view('dictionary/show', [
            'title' => $word['headword'],
            'word'  => $word,
        ]);
    }
}
