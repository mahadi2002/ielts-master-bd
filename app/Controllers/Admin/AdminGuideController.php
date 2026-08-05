<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\GuideRepo;

final class AdminGuideController extends Controller
{
    public function __construct(private GuideRepo $repo = new GuideRepo())
    {
    }

    public function index(Request $request): Response
    {
        $page   = max(1, $request->int('page', 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        return $this->view('admin/guides/index', [
            'title'  => 'Guides',
            'guides' => $this->repo->paginate($limit, $offset),
            'total'  => $this->repo->countAll(),
            'page'   => $page,
            'limit'  => $limit,
        ]);
    }

    public function form(Request $request, ?string $id = null): Response
    {
        $guide = $id !== null ? $this->repo->find((int) $id) : null;
        if ($id !== null && $guide === null) {
            $this->notFound();
        }

        return $this->view('admin/guides/form', ['title' => $guide ? 'Edit Guide' : 'New Guide', 'guide' => $guide]);
    }

    public function store(Request $request): Response
    {
        $this->save($request, null);
        return $this->redirect('/admin/guides');
    }

    public function update(Request $request, string $id): Response
    {
        $this->save($request, (int) $id);
        return $this->redirect('/admin/guides');
    }

    private function save(Request $request, ?int $id): void
    {
        $validator = Validator::make($request->body, [
            'title'    => 'required|max:200',
            'category' => 'required',
            'excerpt'  => 'required|max:300',
            'body_md'  => 'required',
        ], ['title' => 'Title', 'category' => 'Category', 'excerpt' => 'Excerpt', 'body_md' => 'Body']);

        if ($validator->fails()) {
            $validator->flash();
            return;
        }

        $title = (string) $validator->get('title');
        $data  = [
            'slug'           => slugify($title),
            'title'          => $title,
            'category'       => (string) $validator->get('category'),
            'excerpt'        => (string) $validator->get('excerpt'),
            'body_md'        => (string) $validator->get('body_md'),
            'band_relevance' => $request->str('band_relevance') ?: null,
            'is_published'   => $request->input('is_published') !== null,
        ];

        if ($id === null) {
            $this->repo->create($data);
            Session::notify('success', 'গাইড যোগ করা হয়েছে।');
        } else {
            $existing = $this->repo->find($id);
            if ($existing !== null) {
                $data['slug'] = $existing['slug'];
            }
            $this->repo->update($id, $data);
            Session::notify('success', 'গাইড আপডেট করা হয়েছে।');
        }
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->repo->delete((int) $id);
        Session::notify('success', 'গাইড মুছে ফেলা হয়েছে।');
        return $this->redirect('/admin/guides');
    }
}
