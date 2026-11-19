<?php

namespace App\Http\Controllers;

use App\Support\Content\ContentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(private readonly ContentRepository $content) {}

    public function index(): View
    {
        $posts = collect($this->content->slugs('blog', app()->getLocale()))
            ->map(fn ($slug) => $this->content->find('blog/'.$slug))
            ->filter()
            ->sortByDesc(fn ($p) => (string) $p->meta('date'))
            ->values();

        return view('pages.blog.index', ['posts' => $posts]);
    }

    public function show(Request $request): View
    {
        $page = $this->content->get('blog/'.$request->route('slug'));
        abort_if($page->meta('draft'), 404);

        return view('pages.blog.show', ['page' => $page, 'slug' => $request->route('slug')]);
    }
}
