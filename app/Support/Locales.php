<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/**
 * Helpers for the mirrored route tree: every public route is registered once
 * per locale as "{locale}.{name}" (Arabic at "/", English under "/en").
 */
class Locales
{
    public static function all(): array
    {
        return config('yameen.locales');
    }

    public static function current(): string
    {
        return App::getLocale();
    }

    public static function dir(?string $locale = null): string
    {
        return self::all()[$locale ?? self::current()]['dir'] ?? 'rtl';
    }

    /** URL for a localized route name, e.g. lroute('fix.software', ['software' => 'unity']). */
    public static function route(string $name, array $params = [], ?string $locale = null, bool $absolute = true): string
    {
        return route(($locale ?? self::current()).'.'.$name, $params, $absolute);
    }

    /** The current page in another locale, or that locale's home if there is no mirror. */
    public static function switchUrl(string $locale, bool $withQuery = false): string
    {
        $route = Route::current();
        $name = $route?->getName();

        if ($name && preg_match('/^[a-z]{2}\.(.+)$/', $name, $m) && Route::has($locale.'.'.$m[1])) {
            // Only real URI parameters; route defaults must not leak into the query string.
            $url = route($locale.'.'.$m[1], Arr::only($route->parameters(), $route->parameterNames()));
            $query = $withQuery ? request()->getQueryString() : null;

            return $query ? $url.'?'.$query : $url;
        }

        return route($locale.'.home');
    }

    /** Route name without the locale prefix, e.g. "fix.software". */
    public static function baseRouteName(): ?string
    {
        $name = Route::currentRouteName();

        return $name ? preg_replace('/^[a-z]{2}\./', '', $name) : null;
    }
}
