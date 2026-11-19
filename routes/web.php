<?php

use App\Http\Controllers\Api\CreditsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WaylWebhookController;
use App\Http\Controllers\ProAssetController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\CacheGuestPages;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Credits API (JSON). Consume is CSRF-exempt so cached public pages can call it.
Route::prefix('api')->middleware('throttle:60,1')->group(function () {
    Route::get('/credits', [CreditsController::class, 'show'])->name('api.credits');
    Route::post('/credits/consume', [CreditsController::class, 'consume'])->name('api.credits.consume');
    Route::get('/orders/{order}/status', [CheckoutController::class, 'status'])->name('api.orders.status')->middleware('auth');
});

// Wayl payment webhook (signature-verified, CSRF-exempt).
Route::post('/webhooks/wayl', WaylWebhookController::class)->name('webhooks.wayl')->middleware('throttle:120,1');

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', SetLocale::class.':ar'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::post('/orders/{order}/refund', [AdminController::class, 'refund'])->name('orders.refund');
    Route::post('/orders/{order}/paid', [AdminController::class, 'markPaid'])->name('orders.paid');
    Route::post('/orders/{order}/failed', [AdminController::class, 'markFailed'])->name('orders.failed');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'user'])->name('users.show');
    Route::post('/users/{user}/pro', [AdminController::class, 'grantPro'])->name('users.pro');
    Route::post('/users/{user}/pro-until', [AdminController::class, 'setProUntil'])->name('users.pro_until');
    Route::post('/users/{user}/revoke', [AdminController::class, 'revokePro'])->name('users.revoke');
    Route::post('/users/{user}/admin', [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::post('/users/{user}/credits', [AdminController::class, 'resetCredits'])->name('users.credits');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/log', [AdminController::class, 'log'])->name('log');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
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
        ->middleware([SetLocale::class.':'.$locale, CacheGuestPages::class])
        ->group(base_path('routes/localized.php'));
}
