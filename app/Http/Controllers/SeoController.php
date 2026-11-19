<?php

namespace App\Http\Controllers;

use App\Support\PublicPages;
use App\Support\Seo;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(PublicPages $pages): Response
    {
        $locales = array_keys(config('yameen.locales'));
        $default = config('yameen.default_locale');
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'];

        foreach ($pages->paths() as $path) {
            $alternates = [];
            foreach ($locales as $locale) {
                $alternates[$locale] = url(PublicPages::localize($path, $locale));
            }
            foreach ($alternates as $loc) {
                $xml[] = '  <url>';
                $xml[] = '    <loc>'.e($loc).'</loc>';
                foreach ($alternates as $code => $href) {
                    $xml[] = '    <xhtml:link rel="alternate" hreflang="'.$code.'" href="'.e($href).'"/>';
                }
                $xml[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="'.e($alternates[$default]).'"/>';
                $xml[] = '  </url>';
            }
        }
        $xml[] = '</urlset>';

        return response(implode("\n", $xml)."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = Seo::noindex()
            ? ['User-agent: *', 'Disallow: /']
            : ['User-agent: *', 'Disallow: /admin', 'Disallow: /api', 'Disallow: /account', '', 'Sitemap: '.url('/sitemap.xml')];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
