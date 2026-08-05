<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\CollectionRepo;
use App\Repositories\DailyProgressRepo;
use App\Repositories\ProgressRepo;
use App\Repositories\UserRepo;
use App\Repositories\WordRepo;
use App\Services\GoalService;

final class WordController extends Controller
{
    public function __construct(
        private WordRepo $words = new WordRepo(),
        private ProgressRepo $progress = new ProgressRepo(),
        private DailyProgressRepo $dailyProgress = new DailyProgressRepo(),
        private UserRepo $users = new UserRepo(),
        private CollectionRepo $collection = new CollectionRepo(),
    ) {
    }

    /** The daily learning session — a flashcard stack sized to today's remaining goal. */
    public function learn(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $user   = $this->users->find($userId);
        $target = (int) ($user['daily_goal_count'] ?? 5);

        $todayProgress = $this->dailyProgress->today($userId, $target);

        if ((bool) $todayProgress['goal_completed']) {
            return $this->view('app/learn', [
                'title'     => 'Learn',
                'progress'  => $todayProgress,
                'words'     => [],
                'completed' => true,
            ]);
        }

        $remaining = max(1, (int) $todayProgress['goal_target'] - (int) $todayProgress['goal_achieved']);
        $words     = $this->words->nextForLearning($userId, $user['target_band'] !== null ? (float) $user['target_band'] : null, $remaining);

        foreach ($words as &$word) {
            if ($word['synonyms']) {
                $word['synonyms'] = json_decode((string) $word['synonyms'], true);
            }
        }
        unset($word);

        return $this->view('app/learn', [
            'title'     => 'Learn',
            'progress'  => $todayProgress,
            'words'     => $words,
            'completed' => false,
        ]);
    }

    public function markLearned(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $wordId = $request->int('word_id');

        $word = $this->words->find($wordId);
        if ($word === null) {
            return $this->json(['error' => 'শব্দ পাওয়া যায়নি।'], 404);
        }

        $this->progress->upsertLearned($userId, $wordId);

        $result = (new GoalService())->increment($userId);

        return $this->json([
            'success' => true,
            'progress' => [
                'achieved'  => (int) $result['progress']['goal_achieved'],
                'target'    => (int) $result['progress']['goal_target'],
                'completed' => (bool) $result['progress']['goal_completed'],
            ],
            'justCompleted' => $result['justCompleted'],
            'unlockedWord'  => $result['unlockedWord'],
        ]);
    }

    /** Full catalog browse for subscribers, optionally filtered by band. */
    public function index(Request $request): Response
    {
        $band   = $request->input('band') !== null ? $request->int('band') : null;
        $page   = max(1, $request->int('page', 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        $words = $this->words->browse($band, $limit, $offset);
        $total = $this->words->countBrowse($band);

        return $this->view('app/words', [
            'title' => 'Word Catalog',
            'words' => $words,
            'band'  => $band,
            'page'  => $page,
            'total' => $total,
            'limit' => $limit,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $word   = $this->words->findBySlug($slug);
        $userId = (int) $this->currentUserId();

        // An exclusive word is a reward, not catalog browsing — a subscriber
        // who hasn't earned it yet gets the same 404 as a word that doesn't
        // exist, same as any other IDOR-shaped lookup.
        if ($word === null || ((bool) $word['is_exclusive'] && !$this->collection->has($userId, (int) $word['id']))) {
            $this->notFound();
        }

        $this->words->incrementView((int) $word['id']);

        if ($word['synonyms']) {
            $word['synonyms'] = json_decode((string) $word['synonyms'], true);
        }

        $progress = $this->progress->find((int) $this->currentUserId(), (int) $word['id']);

        return $this->view('app/word-show', [
            'title'    => $word['headword'],
            'word'     => $word,
            'progress' => $progress,
        ]);
    }
}
