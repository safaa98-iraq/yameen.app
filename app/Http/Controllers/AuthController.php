<?php

namespace App\Http\Controllers;

use App\Mail\LoginCodeMail;
use App\Models\User;
use App\Services\Credits;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/** Passwordless sign-in: a 6-digit code by email (10 minutes), or Google. */
class AuthController extends Controller
{
    public const CODE_TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect(lroute('account'));
        }

        return view('pages.auth.login', [
            'email' => session('login_email'),
            'google' => (bool) config('services.google.client_id'),
        ]);
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:190']]);
        $email = Str::lower(trim($data['email']));

        $key = 'login-code:'.Credits::hmac($email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withInput()->withErrors(['email' => __('ui.auth.too_many', ['minutes' => ceil(RateLimiter::availableIn($key) / 60)])]);
        }
        RateLimiter::hit($key, 600);

        $code = (string) random_int(100000, 999999);
        DB::table('login_codes')->where('email', $email)->whereNull('used_at')->delete();
        DB::table('login_codes')->insert([
            'email' => $email,
            'code_hash' => Credits::hmac('login:'.$email.':'.$code),
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Mail::to($email)->send(new LoginCodeMail($code, app()->getLocale()));

        return redirect(lroute('login'))->with('login_email', $email)->with('status', __('ui.auth.code_sent', ['email' => $email]));
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc'],
            'code' => ['required', 'digits:6'],
        ]);
        $email = Str::lower(trim($data['email']));

        $row = DB::table('login_codes')->where('email', $email)->whereNull('used_at')->latest('id')->first();
        $fail = fn () => redirect(lroute('login'))->with('login_email', $email)->withErrors(['code' => __('ui.auth.bad_code')]);

        if (! $row || now()->greaterThan($row->expires_at) || $row->attempts >= self::MAX_ATTEMPTS) {
            return $fail();
        }
        if (! hash_equals($row->code_hash, Credits::hmac('login:'.$email.':'.$data['code']))) {
            DB::table('login_codes')->where('id', $row->id)->increment('attempts');

            return $fail();
        }

        DB::table('login_codes')->where('id', $row->id)->update(['used_at' => now()]);
        $user = User::firstOrCreate(['email' => $email]);
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $this->signIn($request, $user);
    }

    public function googleRedirect(Request $request): RedirectResponse
    {
        abort_unless(config('services.google.client_id'), 404);
        $request->session()->put('auth_locale', $request->query('locale', config('yameen.default_locale')));

        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        abort_unless(config('services.google.client_id'), 404);
        app()->setLocale($request->session()->pull('auth_locale', config('yameen.default_locale')));

        try {
            $g = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect(lroute('login'))->withErrors(['email' => __('ui.auth.google_failed')]);
        }

        $email = Str::lower((string) $g->getEmail());
        $user = User::where('google_id', $g->getId())->first() ?? User::firstOrCreate(['email' => $email]);
        $user->forceFill([
            'google_id' => $g->getId(),
            'name' => $user->name ?: $g->getName(),
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();

        return $this->signIn($request, $user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(lroute('home'));
    }

    private function signIn(Request $request, User $user): RedirectResponse
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(lroute('account'));
    }
}
