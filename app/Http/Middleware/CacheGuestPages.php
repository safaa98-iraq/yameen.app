<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full-page cache for public pages viewed by guests. Signed-in users (who may
 * be Pro and see no ads) always get a fresh page. Per-user data on cached
 * pages (credits) is loaded by JavaScript. The cache is versioned: changing
 * settings or fonts bumps the version (see bump()).
 */
class CacheGuestPages
{
    public const TTL = 600;

    /** Route names (without locale prefix) that may be cached. */
    private const ROUTES = [
        'home', 'fix', 'fix.software', 'pdf-fix', 'garbled', 'clean', 'export', 'batch', 'pricing',
        'fonts', 'fonts.*', 'about', 'privacy', 'terms', 'contact', 'blog', 'blog.*',
    ];

    public static function bump(): void
    {
        Cache::forever('page-cache:version', (int) Cache::get('page-cache:version', 0) + 1);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->cacheable($request)) {
            return $next($request);
        }

        $key = 'page:'.Cache::get('page-cache:version', 0).':'.sha1($request->fullUrl());
        if ($hit = Cache::get($key)) {
            return response($hit['body'], 200, ['Content-Type' => $hit['type'], 'X-Page-Cache' => 'HIT']);
        }

        $response = $next($request);
        if ($response->getStatusCode() === 200 && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            Cache::put($key, ['body' => $response->getContent(), 'type' => $response->headers->get('Content-Type')], self::TTL);
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }

    private function cacheable(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->user()) {
            return false;
        }
        $session = $request->hasSession() ? $request->session() : null;
        if ($session && ($session->has('errors') || $session->has('status') || $session->has('_old_input'))) {
            return false;
        }
        $name = Locales::baseRouteName();

        return $name !== null && Str::is(self::ROUTES, $name);
    }
}
