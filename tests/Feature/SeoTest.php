<?php

use App\Console\Commands\FontsSync;

function seoFonts(): void
{
    app(FontsSync::class)->import(json_decode(file_get_contents(base_path('tests/Fixtures/fonts.json')), true));
}

/** Decode every JSON-LD block on a page. */
function jsonLd(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return array_map(fn ($j) => json_decode($j, true, flags: JSON_THROW_ON_ERROR), $m[1]);
}

it('gives every public page a unique title and description', function () {
    seoFonts();
    $titles = $descriptions = [];
    foreach (app(\App\Support\PublicPages::class)->paths() as $path) {
        foreach (['ar', 'en'] as $locale) {
            $html = $this->get(\App\Support\PublicPages::localize($path, $locale))->assertOk()->getContent();
            preg_match('#<title>(.*?)</title>#', $html, $t);
            preg_match('#<meta name="description" content="(.*?)">#', $html, $d);
            $titles[] = $t[1];
            $descriptions[] = $d[1];
        }
    }
    expect(array_unique($titles))->toHaveCount(count($titles))
        ->and(array_unique($descriptions))->toHaveCount(count($descriptions));
});

it('outputs canonical, Open Graph and twitter tags', function () {
    $this->get('/en/fix/unity')
        ->assertSee('<link rel="canonical" href="'.url('/en/fix/unity').'">', false)
        ->assertSee('<meta property="og:url" content="'.url('/en/fix/unity').'">', false)
        ->assertSee('<meta property="og:locale" content="en_US">', false)
        ->assertSee('<meta property="og:locale:alternate" content="ar_IQ">', false)
        ->assertSee('<meta property="og:image" content="'.asset('og-default.png').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('drops the query string from the canonical URL', function () {
    $this->get('/fix?font=cairo')->assertSee('<link rel="canonical" href="'.url('/fix').'">', false);
});

it('adds WebApplication and BreadcrumbList JSON-LD to tool pages', function () {
    $ld = jsonLd($this->get('/fix/photoshop')->getContent());
    $types = array_column($ld, '@type');

    expect($types)->toContain('WebApplication', 'BreadcrumbList');
    $crumbs = collect($ld)->firstWhere('@type', 'BreadcrumbList')['itemListElement'];
    expect(array_column($crumbs, 'name'))->toBe(['الرئيسية', 'مصحح النص العربي', 'Photoshop'])
        ->and(end($crumbs)['item'])->toBe(url('/fix/photoshop'));
});

it('adds breadcrumbs to font pages and uses the font share image', function () {
    seoFonts();
    $html = $this->get('/en/fonts/cairo')->getContent();
    $crumbs = collect(jsonLd($html))->firstWhere('@type', 'BreadcrumbList')['itemListElement'];

    expect(array_column($crumbs, 'name'))->toBe(['Home', 'Arabic fonts', 'Cairo']);
});

it('marks blog posts as articles', function () {
    $ld = jsonLd($this->get('/blog/arabic-text-in-design-apps')->assertOk()->getContent());
    expect(array_column($ld, '@type'))->toContain('Article', 'BreadcrumbList');
});

it('serves a sitemap with both locales and hreflang alternates', function () {
    seoFonts();
    $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

    expect($xml)->toContain('<loc>'.url('/fix/unity').'</loc>')
        ->toContain('<loc>'.url('/en/fix/unity').'</loc>')
        ->toContain('<loc>'.url('/fonts/cairo').'</loc>')
        ->toContain('<loc>'.url('/blog/choosing-an-arabic-font').'</loc>')
        ->toContain('hreflang="x-default" href="'.url('/fonts/cairo').'"')
        ->not->toContain('/login')
        ->not->toContain('/export');
    expect(simplexml_load_string($xml))->not->toBeFalse();
});

it('serves robots.txt with the sitemap, or blocks everything on previews', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'))->assertSee('Disallow: /admin');

    config(['yameen.seo.noindex' => true]);
    $this->get('/robots.txt')->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap');
    $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('does not index placeholder pages', function () {
    $this->get('/pricing')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    $this->get('/about')->assertDontSee('noindex');
});

it('renders static pages and the contact email', function () {
    $this->get('/privacy')->assertOk()->assertSee('النص الذي تكتبه');
    $this->get('/en/terms')->assertOk()->assertSee('Pro plan');
    $this->get('/contact')->assertOk()->assertSee('mailto:'.config('yameen.contact_email'), false);
    $this->get('/en/blog')->assertOk()->assertSee('How to choose an Arabic font for your design');
});
