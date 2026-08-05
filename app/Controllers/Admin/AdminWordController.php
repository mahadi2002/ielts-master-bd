<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\WordRepo;

final class AdminWordController extends Controller
{
    public function __construct(private WordRepo $repo = new WordRepo())
    {
    }

    public function index(Request $request): Response
    {
        $page   = max(1, $request->int('page', 1));
        $limit  = 50;
        $offset = ($page - 1) * $limit;

        return $this->view('admin/words/index', [
            'title' => 'Words',
            'words' => $this->repo->paginate($limit, $offset),
            'total' => $this->repo->countAll(),
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    public function form(Request $request, ?string $id = null): Response
    {
        $word = $id !== null ? $this->repo->find((int) $id) : null;
        if ($id !== null && $word === null) {
            $this->notFound();
        }
        if ($word !== null && $word['synonyms']) {
            $word['synonyms'] = json_decode((string) $word['synonyms'], true);
        }

        return $this->view('admin/words/form', ['title' => $word ? 'Edit Word' : 'New Word', 'word' => $word]);
    }

    public function store(Request $request): Response
    {
        $this->save($request, null);
        return $this->redirect('/admin/words');
    }

    public function update(Request $request, string $id): Response
    {
        $this->save($request, (int) $id);
        return $this->redirect('/admin/words');
    }

    private function save(Request $request, ?int $id): void
    {
        $validator = Validator::make($request->body, [
            'headword'         => 'required|max:100',
            'definition_en'    => 'required',
            'ielts_band_level' => 'required|int',
        ], ['headword' => 'Headword', 'definition_en' => 'Definition (English)', 'ielts_band_level' => 'Band Level']);

        if ($validator->fails()) {
            $validator->flash();
            return;
        }

        $headword = (string) $validator->get('headword');
        $synonyms = array_filter(array_map('trim', explode(',', $request->str('synonyms'))));

        $data = [
            'slug'             => slugify($headword) . '-' . substr(md5($headword . microtime()), 0, 4),
            'headword'         => $headword,
            'ipa'              => $request->str('ipa') ?: null,
            'part_of_speech'   => $request->str('part_of_speech') ?: null,
            'definition_en'    => (string) $validator->get('definition_en'),
            'definition_bn'    => $request->str('definition_bn') ?: null,
            'example_sentence' => $request->str('example_sentence') ?: null,
            'synonyms'         => $synonyms ?: null,
            'ielts_band_level' => (int) $validator->get('ielts_band_level'),
            'task_tag'         => $request->str('task_tag') ?: null,
            'is_exclusive'     => $request->input('is_exclusive') !== null,
            'frequency_rank'   => $request->str('frequency_rank') !== '' ? $request->int('frequency_rank') : null,
        ];

        if ($id === null) {
            $this->repo->create($data);
            Session::notify('success', 'শব্দ যোগ করা হয়েছে।');
        } else {
            $existing = $this->repo->find($id);
            if ($existing !== null) {
                $data['slug'] = $existing['slug']; // keep the slug stable across edits
            }
            $this->repo->update($id, $data);
            Session::notify('success', 'শব্দ আপডেট করা হয়েছে।');
        }
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->repo->delete((int) $id);
        Session::notify('success', 'শব্দ মুছে ফেলা হয়েছে।');
        return $this->redirect('/admin/words');
    }
}
