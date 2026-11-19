<?php

use App\Console\Commands\FontsSync;
use App\Models\Font;


function importFixture(): int
{
    return app(FontsSync::class)->import(json_decode(file_get_contents(base_path('tests/Fixtures/fonts.json')), true));
}

it('imports the manifest, applies overrides and hides families', function () {
    expect(importFixture())->toBe(3); // cascadia-code is hidden by overrides.json

    $cairo = Font::where('slug', 'cairo')->first();
    expect($cairo->presentation_forms)->toBeTrue()
        ->and($cairo->popularity)->toBe(1)
        ->and($cairo->weights)->toBe([400, 700]);

    $reem = Font::where('slug', 'reem-kufi')->first();
    expect($reem->style)->toBe('kufi') // override
        ->and($reem->presentation_forms)->toBeFalse(); // 40% coverage is not enough

    expect(Font::where('slug', 'cascadia-code')->exists())->toBeFalse();
});

it('is idempotent and removes families that disappeared', function () {
    importFixture();
    importFixture();
    expect(Font::count())->toBe(3);

    app(FontsSync::class)->import(['fonts' => [json_decode(file_get_contents(base_path('tests/Fixtures/fonts.json')), true)['fonts'][0]]]);
    expect(Font::pluck('slug')->all())->toBe(['cairo']);
});

it('lists fonts with lazy-loading cards', function () {
    importFixture();

    $this->get('/fonts')
        ->assertOk()
        ->assertSee('Cairo')
        ->assertSee('x-data="fontCard(', false)
        ->assertDontSee('Source Sans 3'); // Latin pairing fonts are not listed
});

it('filters by style', function () {
    importFixture();

    $this->get('/fonts/style/kufi')->assertOk()->assertSee('خطوط كوفي');
    $this->get('/en/fonts/style/kufi')->assertOk()->assertSee('Kufi fonts');
});

it('renders a font page with CSS, licence and the fixer', function () {
    importFixture();

    $this->get('/fonts/cairo')
        ->assertOk()
        ->assertSee('<title>خط Cairo', false)
        ->assertSee("font-family: &#039;Cairo&#039;", false)
        ->assertSee('/font-files/cairo/OFL.txt')
        ->assertSee('https://fonts.google.com/specimen/Cairo')
        ->assertSee('x-data="fixer(', false);
});

it('explains when a font cannot be used in apps', function () {
    importFixture();

    $this->get('/fonts/reem-kufi')->assertOk()->assertSee('لا يحتوي على أشكال الحروف الموصولة');
});

it('returns 404 for unknown or Latin-only fonts', function () {
    importFixture();

    $this->get('/fonts/nope')->assertNotFound();
    $this->get('/fonts/source-sans-3')->assertNotFound();
});

it('offers only app-ready fonts in the fixer and honours ?font=', function () {
    importFixture();

    $html = $this->get('/fix?font=cairo')->assertOk()->getContent();
    expect($html)->toContain('<option value="cairo" selected>')
        ->and($html)->not->toContain('<option value="reem-kufi"');
});

it('renders compare and pairing pages', function () {
    importFixture();

    $this->get('/fonts/compare?a=cairo&b=reem-kufi')->assertOk()->assertSee('Reem Kufi');
    $this->get('/fonts/pairing')->assertOk()->assertSee('Source Sans 3');
});

it('generates a CSS snippet per weight', function () {
    importFixture();
    $css = Font::where('slug', 'cairo')->first()->cssSnippet(700);

    expect($css)->toContain("src: url('fonts/cairo-700.woff2')")
        ->toContain('font-weight: 700;')
        ->not->toContain('cairo-400');
});

it('shows app-ready fonts with a copy button on the home page', function () {
    importFixture();

    $this->get('/')
        ->assertOk()
        ->assertSee('x-data="quickFix"', false)
        ->assertSee('Cairo')
        ->assertDontSee('id="font-reem-kufi"', false) // not usable in apps
        ->assertSee('copyForApp()', false);
});

it('does not link to pages that are not built yet', function () {
    $html = $this->get('/')->getContent();

    foreach (['/export', '/login', '/pricing', '/privacy'] as $path) {
        expect($html)->not->toContain('href="'.url($path).'"');
    }
});
