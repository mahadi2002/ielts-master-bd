<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\UserCollection;

final class CollectionController
{
    public function index(Request $request): void
    {
        $userId = (string) Session::userId();
        $words = UserCollection::forUser($userId);

        foreach ($words as &$word) {
            if ($word['synonyms']) {
                $word['synonyms'] = json_decode($word['synonyms'], true);
            }
        }
        unset($word);

        Response::html(View::render('collection/index', ['words' => $words]));
    }
}
