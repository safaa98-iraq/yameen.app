<?php

use App\Http\Controllers\Api\CreditsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProAssetController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Credits API (JSON). Consume is CSRF-exempt so cached public pages can call it.
Route::prefix('api')->middleware('throttle:60,1')->group(function () {
    Route::get('/credits', [CreditsController::class, 'show'])->name('api.credits');
    Route::post('/credits/consume', [CreditsController::class, 'consume'])->name('api.credits.consume');
});

// Pro-only JavaScript modules (SVG export, batch), served to Pro subscribers only.
Route::get('/pro/{name}.js', ProAssetController::class)->name('pro.asset')->where('name', '[a-z]+');

// Google sign-in (one fixed callback URL; the locale travels in the session).
Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');

/*
| Every public page exists once per locale: Arabic at the root, English
| under /en with the same slugs. Route names are "{locale}.{name}".
*/
foreach (config('yameen.locales') as $locale => $meta) {
    Route::prefix($meta['prefix'])
        ->name($locale.'.')
        ->middleware(SetLocale::class.':'.$locale)
        ->group(base_path('routes/localized.php'));
}
