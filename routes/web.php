<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

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
