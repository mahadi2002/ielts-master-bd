<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CollectionRepo;

final class CollectionController extends Controller
{
    public function index(Request $request): Response
    {
        $words = (new CollectionRepo())->forUser((int) $this->currentUserId());

        foreach ($words as &$word) {
            if ($word['synonyms']) {
                $word['synonyms'] = json_decode((string) $word['synonyms'], true);
            }
        }
        unset($word);

        return $this->view('app/collection', ['title' => 'My Collection', 'words' => $words]);
    }
}
