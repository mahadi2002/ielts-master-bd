<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\QuestionRepo;
use App\Repositories\WordRepo;

/** The ask-a-question UGC loop — moderated, answered by admins/editors, not other users. */
final class QaController extends Controller
{
    public function __construct(private QuestionRepo $repo = new QuestionRepo())
    {
    }

    public function index(Request $request): Response
    {
        return $this->view('app/qa-index', [
            'title'     => 'প্রশ্ন-উত্তর',
            'answered'  => $this->repo->answered(30),
            'myQuestions' => $this->repo->forUser((int) $this->currentUserId()),
        ]);
    }

    public function askForm(Request $request): Response
    {
        $wordSlug = $request->str('word');
        $word     = $wordSlug !== '' ? (new WordRepo())->findBySlug($wordSlug) : null;

        return $this->view('app/qa-ask', ['title' => 'প্রশ্ন করুন', 'word' => $word]);
    }

    public function store(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'title' => 'required|min:8|max:200',
            'body'  => 'required|min:10|max:2000',
        ], ['title' => 'শিরোনাম', 'body' => 'বিস্তারিত']);

        if ($validator->fails()) {
            $validator->flash();
            return $this->redirect('/app/qa/ask');
        }

        $wordId = null;
        $slug   = $request->str('word_slug');
        if ($slug !== '') {
            $word   = (new WordRepo())->findBySlug($slug);
            $wordId = $word !== null ? (int) $word['id'] : null;
        }

        $id = $this->repo->create(
            (int) $this->currentUserId(),
            $wordId,
            (string) $validator->get('title'),
            (string) $validator->get('body')
        );

        Session::notify('success', 'প্রশ্নটি জমা হয়েছে। উত্তর পেলে এখানেই দেখতে পাবেন।');
        return $this->redirect('/app/qa/' . $id);
    }

    public function show(Request $request, string $id): Response
    {
        $question = $this->repo->find((int) $id);
        if ($question === null) {
            $this->notFound();
        }

        return $this->view('app/qa-show', ['title' => $question['title'], 'question' => $question]);
    }
}
