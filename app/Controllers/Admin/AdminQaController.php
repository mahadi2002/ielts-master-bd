<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\QuestionRepo;

final class AdminQaController extends Controller
{
    public function __construct(private QuestionRepo $repo = new QuestionRepo())
    {
    }

    public function index(Request $request): Response
    {
        return $this->view('admin/qa/index', [
            'title' => 'Q&A',
            'open'  => $this->repo->open(50),
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $question = $this->repo->find((int) $id);
        if ($question === null) {
            $this->notFound();
        }

        return $this->view('admin/qa/show', ['title' => $question['title'], 'question' => $question]);
    }

    public function update(Request $request, string $id): Response
    {
        $validator = Validator::make($request->body, ['answer' => 'required|min:5|max:2000'], ['answer' => 'উত্তর']);

        if ($validator->fails()) {
            $validator->flash();
            return $this->redirect('/admin/qa/' . $id);
        }

        $this->repo->answer((int) $id, (int) $this->currentUserId(), (string) $validator->get('answer'));

        Session::notify('success', 'উত্তর দেওয়া হয়েছে।');
        return $this->redirect('/admin/qa');
    }
}
