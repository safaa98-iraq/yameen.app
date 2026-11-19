<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Demo accounts for trying every role without email codes. Only active when
 * DEMO_MODE=true; otherwise every route here answers 404.
 */
class DemoController extends Controller
{
    public const ACCOUNTS = [
        'admin' => 'admin@demo.yameen.app',
        'free' => 'free@demo.yameen.app',
        'monthly' => 'monthly@demo.yameen.app',
        'lifetime' => 'lifetime@demo.yameen.app',
    ];

    public function show(): View
    {
        abort_unless(config('yameen.demo'), 404);

        return view('pages.demo', ['accounts' => self::ACCOUNTS]);
    }

    public function login(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('yameen.demo') && isset(self::ACCOUNTS[$role]), 404);
        $user = self::prepare($role);
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect($role === 'admin' ? route('admin.dashboard') : lroute('account'));
    }

    /** Create the account if needed and reset it to its role. */
    public static function prepare(string $role): User
    {
        $user = User::firstOrCreate(['email' => self::ACCOUNTS[$role]], ['name' => 'Demo '.$role, 'email_verified_at' => now()]);
        $user->forceFill([
            'is_admin' => $role === 'admin',
            'pro_lifetime' => $role === 'lifetime' || $role === 'admin',
            'pro_until' => $role === 'monthly' ? now()->addDays(30) : null,
        ])->save();

        return $user;
    }
}
