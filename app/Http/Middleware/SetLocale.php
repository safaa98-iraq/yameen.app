<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        if (! array_key_exists($locale, config('yameen.locales'))) {
            abort(404);
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
