<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('pages.account', [
            'user' => $user,
            'orders' => $user->orders()->latest()->get(),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => ['required', 'in:'.$request->user()->email]]);
        $user = $request->user();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $user->delete();

        return redirect(lroute('home'))->with('status', __('ui.account.deleted'));
    }
}
