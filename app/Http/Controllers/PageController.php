<?php

namespace App\Http\Controllers;

use App\Support\Content\ContentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    /** About, privacy, terms, contact: Markdown from resources/content/{locale}/{page}.md. */
    public function show(Request $request, ContentRepository $content): View
    {
        $slug = $request->route('page');

        return view('pages.static', ['page' => $content->get($slug), 'slug' => $slug]);
    }

    /** Temporary page for routes whose tools arrive in later phases. Not indexed. */
    public function placeholder(Request $request): View
    {
        return view('pages.placeholder', ['page' => $request->route('page')]);
    }
}
