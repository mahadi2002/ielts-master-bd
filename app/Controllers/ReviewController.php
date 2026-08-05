<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProgressRepo;
use App\Services\SpacedRepetitionService;

final class ReviewController extends Controller
{
    public function __construct(private ProgressRepo $repo = new ProgressRepo())
    {
    }

    public function queue(Request $request): Response
    {
        $words = $this->repo->dueForReview((int) $this->currentUserId());

        return $this->view('app/review', ['title' => 'Review Queue', 'words' => $words]);
    }

    public function answer(Request $request): Response
    {
        $userId     = (int) $this->currentUserId();
        $progressId = $request->int('progress_id');
        $rating     = $request->str('rating'); // again|hard|good|easy

        $progress = $this->repo->findById($progressId, $userId);
        if ($progress === null) {
            return $this->json(['error' => 'পাওয়া যায়নি।'], 404);
        }

        $updated = (new SpacedRepetitionService($this->repo))->submitAnswer($progress, $rating);

        return $this->json(['success' => true, 'srs' => $updated]);
    }
}
