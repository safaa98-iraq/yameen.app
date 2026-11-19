<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Support\FontCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Saved fonts and texts (Pro). */
class FavoriteController extends Controller
{
    public function index(Request $request, FontCatalog $catalog): View
    {
        $favorites = $request->user()->favorites()->latest()->get();

        return view('pages.favorites', [
            'pro' => $request->user()->isPro(),
            'fonts' => $favorites->where('type', 'font')->map(fn ($f) => $catalog->find($f->value) ? ['fav' => $f, 'font' => $catalog->find($f->value)] : null)->filter()->values(),
            'texts' => $favorites->where('type', 'text')->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isPro(), 403);
        $data = $request->validate([
            'type' => ['required', 'in:font,text'],
            'value' => ['required', 'string', 'max:2000'],
        ]);
        $request->user()->favorites()->firstOrCreate(
            ['type' => $data['type'], 'value_hash' => hash('sha256', $data['value'])],
            ['value' => $data['value']],
        );

        return back()->with('status', __('ui.favorites.saved'));
    }

    public function destroy(Request $request, Favorite $favorite): RedirectResponse
    {
        abort_unless($favorite->user_id === $request->user()->id, 404);
        $favorite->delete();

        return back();
    }
}
