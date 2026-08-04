<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\UserWordProgress;
use App\Services\SpacedRepetitionService;

final class ReviewController
{
    public function queue(Request $request): void
    {
        $userId = (string) Session::userId();
        $words = UserWordProgress::dueForReview($userId);

        Response::html(View::render('review/queue', ['words' => $words]));
    }

    public function submitAnswer(Request $request): void
    {
        $userId = (string) Session::userId();
        $progressId = (string) $request->input('progress_id');
        $rating = (string) $request->input('rating'); // again|hard|good|easy

        $stmt = \App\Core\Db::pdo()->prepare('SELECT * FROM user_word_progress WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $progressId, 'u' => $userId]);
        $progress = $stmt->fetch();

        if (!$progress) {
            Response::json(['error' => 'পাওয়া যায়নি।'], 404);
            return;
        }

        $srs = new SpacedRepetitionService();
        $updated = $srs->submitAnswer($progress, $rating);

        Response::json(['success' => true, 'srs' => $updated]);
    }
}
