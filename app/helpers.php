<?php

use App\Support\Locales;

if (! function_exists('lroute')) {
    function lroute(string $name, array $params = [], ?string $locale = null, bool $absolute = true): string
    {
        return Locales::route($name, $params, $locale, $absolute);
    }
}

if (! function_exists('route_is')) {
    /** True when the current localized route matches one of the given base names (wildcards allowed). */
    function route_is(string ...$patterns): bool
    {
        $base = Locales::baseRouteName();

        return $base !== null && \Illuminate\Support\Str::is($patterns, $base);
    }
}
