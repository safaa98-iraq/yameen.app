<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Loaded once per locale from routes/web.php. Do not add locale prefixes here.

$software = implode('|', array_keys(config('yameen.software')));
$styles = implode('|', config('yameen.font_styles'));

Route::get('/', [PageController::class, 'home'])->name('home');

// Tools (implemented in phase 1+; placeholders until then)
Route::get('/fix', [PageController::class, 'placeholder'])->name('fix')->defaults('page', 'fix');
Route::get('/fix/{software}', [PageController::class, 'placeholder'])->name('fix.software')
    ->where('software', $software)->defaults('page', 'fix');
Route::get('/pdf-fix', [PageController::class, 'placeholder'])->name('pdf-fix')->defaults('page', 'pdf-fix');
Route::get('/clean', [PageController::class, 'placeholder'])->name('clean')->defaults('page', 'clean');

// Fonts (phase 2)
Route::get('/fonts', [PageController::class, 'placeholder'])->name('fonts')->defaults('page', 'fonts');
Route::get('/fonts/compare', [PageController::class, 'placeholder'])->name('fonts.compare')->defaults('page', 'fonts-compare');
Route::get('/fonts/pairing', [PageController::class, 'placeholder'])->name('fonts.pairing')->defaults('page', 'fonts-pairing');
Route::get('/fonts/style/{style}', [PageController::class, 'placeholder'])->name('fonts.style')
    ->where('style', $styles)->defaults('page', 'fonts');

// Export (phase 5)
Route::get('/export', [PageController::class, 'placeholder'])->name('export')->defaults('page', 'export');

// Account and plans (phases 4 and 6)
Route::get('/pricing', [PageController::class, 'placeholder'])->name('pricing')->defaults('page', 'pricing');
Route::get('/login', [PageController::class, 'placeholder'])->name('login')->defaults('page', 'login');

// Static pages (phase 3)
foreach (['about', 'privacy', 'terms', 'contact'] as $page) {
    Route::get('/'.$page, [PageController::class, 'placeholder'])->name($page)->defaults('page', $page);
}
