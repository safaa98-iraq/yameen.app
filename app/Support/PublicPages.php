<?php

namespace App\Support;

use App\Support\Content\ContentRepository;

/** Every public, indexable page, used by the sitemap and the static export. */
class PublicPages
{
    public const STATIC_PAGES = ['about', 'privacy', 'terms', 'contact'];

    public function __construct(
        private readonly FontCatalog $fonts,
        private readonly ContentRepository $content,
    ) {}

    /** Locale-neutral paths, e.g. "/", "/fix/unity", "/fonts/cairo". */
    public function paths(): array
    {
        $paths = ['/', '/fix', '/pdf-fix', '/garbled-text', '/clean', '/fonts', '/fonts/compare', '/fonts/pairing', '/blog'];
        foreach (array_keys(config('yameen.software')) as $software) {
            $paths[] = '/fix/'.$software;
        }
        foreach (config('yameen.font_styles') as $style) {
            $paths[] = '/fonts/style/'.$style;
        }
        foreach ($this->fonts->arabic() as $font) {
            $paths[] = '/fonts/'.$font->slug;
        }
        foreach (self::STATIC_PAGES as $page) {
            $paths[] = '/'.$page;
        }
        foreach ($this->content->slugs('blog') as $slug) {
            $paths[] = '/blog/'.$slug;
        }

        return $paths;
    }

    /** Path under a locale prefix: ("/fix", "en") → "/en/fix". */
    public static function localize(string $path, string $locale): string
    {
        $prefix = config('yameen.locales.'.$locale.'.prefix');
        if (! $prefix) {
            return $path;
        }

        return $path === '/' ? '/'.$prefix : '/'.$prefix.$path;
    }
}
