<?php

it('renders the fixer page with content and FAQ', function () {
    $this->get('/fix')
        ->assertOk()
        ->assertSee('مصحح النص العربي')
        ->assertSee('x-data="fixer(', false)
        ->assertSee('أسئلة شائعة');
});

it('renders every software page with its paste instructions in both locales', function (string $slug, string $name) {
    $this->get('/fix/'.$slug)
        ->assertOk()
        ->assertSee('طريقة اللصق في '.$name)
        ->assertSee('<title>تصحيح النص العربي في '.$name, false);

    $this->get('/en/fix/'.$slug)
        ->assertOk()
        ->assertSee('How to paste in '.$name);
})->with(collect((require __DIR__.'/../../config/yameen.php')['software'])->map(fn ($name, $slug) => [$slug, $name])->values()->all());

it('gives each software page a unique title and description', function () {
    $titles = collect(config('yameen.software'))->keys()->map(function ($slug) {
        preg_match('/<title>(.*?)<\/title>/', $this->get('/fix/'.$slug)->getContent(), $m);

        return $m[1];
    });

    expect($titles->unique())->toHaveCount($titles->count());
});

it('renders the PDF fix and cleaner tools', function () {
    $this->get('/pdf-fix')->assertOk()->assertSee('x-data="pdfFixer"', false)->assertSee('اعكس الترتيب');
    $this->get('/en/clean')->assertOk()->assertSee('x-data="cleaner"', false)->assertSee('Remove tatweel');
});

it('does not render raw HTML from Markdown content', function () {
    $page = app(\App\Support\Content\ContentRepository::class)
        ->parse('x', 'ar', "---\ntitle: T\n---\n\n## قسم\n\n<script>alert(1)</script> نص");

    expect($page->sections[0]['html'])->not->toContain('<script>')
        ->and($page->sections[0]['title'])->toBe('قسم');
});

it('serves content pages from a serializing cache store', function () {
    config(['cache.default' => 'file']);
    cache()->flush();

    $this->get('/fix/unity')->assertOk();
    $this->get('/fix/unity')->assertOk()->assertSee('TextMeshPro');
});
