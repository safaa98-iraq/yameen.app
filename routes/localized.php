<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\FontController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

// Loaded once per locale from routes/web.php. Do not add locale prefixes here.

$software = implode('|', array_keys(config('yameen.software')));
$styles = implode('|', config('yameen.font_styles'));

Route::get('/', [PageController::class, 'home'])->name('home');

// Text tools
Route::get('/fix', [ToolController::class, 'fix'])->name('fix');
Route::get('/fix/{software}', [ToolController::class, 'fix'])->name('fix.software')->where('software', $software);
Route::get('/pdf-fix', [ToolController::class, 'pdfFix'])->name('pdf-fix');
Route::get('/clean', [ToolController::class, 'clean'])->name('clean');

// Fonts
Route::get('/fonts', [FontController::class, 'index'])->name('fonts');
Route::get('/fonts/compare', [FontController::class, 'compare'])->name('fonts.compare');
Route::get('/fonts/pairing', [FontController::class, 'pairing'])->name('fonts.pairing');
Route::get('/fonts/style/{style}', [FontController::class, 'index'])->name('fonts.style')->where('style', $styles);
Route::get('/fonts/{font}', [FontController::class, 'show'])->name('fonts.show')->where('font', '[a-z0-9-]+');

// Export (phase 5)
Route::get('/export', [PageController::class, 'placeholder'])->name('export')->defaults('page', 'export');

// Account and plans (phases 4 and 6)
Route::get('/pricing', [PageController::class, 'placeholder'])->name('pricing')->defaults('page', 'pricing');
Route::get('/login', [PageController::class, 'placeholder'])->name('login')->defaults('page', 'login');

// Static pages and blog
foreach (['about', 'privacy', 'terms', 'contact'] as $page) {
    Route::get('/'.$page, [PageController::class, 'show'])->name($page)->defaults('page', $page);
}
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9-]+');
