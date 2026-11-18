<?php

namespace App\Http\Controllers;

use App\Support\FontCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FontController extends Controller
{
    public function __construct(private readonly FontCatalog $fonts) {}

    public function index(Request $request): View
    {
        return view('pages.fonts.index', [
            'fonts' => $this->fonts->arabic(),
            'activeStyle' => $request->route('style'),
        ]);
    }

    public function show(Request $request): View
    {
        $font = $this->fonts->find($request->route('font')) ?? abort(404);

        return view('pages.fonts.show', [
            'font' => $font,
            'related' => $this->fonts->related($font),
        ]);
    }

    public function compare(Request $request): View
    {
        $all = $this->fonts->arabic();
        $a = $this->fonts->find($request->query('a')) ?? $all->get(0);
        $b = $this->fonts->find($request->query('b')) ?? $all->first(fn ($f) => $f->isNot($a));

        return view('pages.fonts.compare', ['fonts' => $all, 'a' => $a, 'b' => $b]);
    }

    public function pairing(): View
    {
        return view('pages.fonts.pairing', ['pairs' => $this->fonts->pairings()]);
    }
}
