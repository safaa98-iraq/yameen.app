<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    /** Temporary page for routes whose tools arrive in later phases. */
    public function placeholder(Request $request): View
    {
        return view('pages.placeholder', ['page' => $request->route('page')]);
    }
}
