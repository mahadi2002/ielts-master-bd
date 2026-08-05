<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Markdown;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\GuideRepo;

final class GuideController extends Controller
{
    public function __construct(private GuideRepo $repo = new GuideRepo())
    {
    }

    /** Public — title + excerpt only, the free teaser. body_md never leaves the database for this path. */
    public function index(Request $request): Response
    {
        $category = $request->str('category') ?: null;

        return $this->view('guides/index', [
            'title'    => 'Guides',
            'guides'   => $this->repo->publishedTeaser($category),
            'category' => $category,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $guide = $this->repo->findTeaserBySlug($slug);
        if ($guide === null) {
            $this->notFound();
        }

        return $this->view('guides/show', [
            'title'         => $guide['title'],
            'guide'         => $guide,
            'isSubscribed'  => $this->isSubscribed(),
        ]);
    }

    /** Gated — the same catalog, full body rendered from Markdown. */
    public function appIndex(Request $request): Response
    {
        $category = $request->str('category') ?: null;

        return $this->view('app/guides', [
            'title'    => 'Guides',
            'guides'   => $this->repo->published($category),
            'category' => $category,
        ]);
    }

    public function appShow(Request $request, string $slug): Response
    {
        $guide = $this->repo->findBySlug($slug);
        if ($guide === null) {
            $this->notFound();
        }

        $this->repo->incrementView((int) $guide['id']);

        return $this->view('app/guide-show', [
            'title'    => $guide['title'],
            'guide'    => $guide,
            'bodyHtml' => Markdown::toHtml($guide['body_md']),
        ]);
    }
}
