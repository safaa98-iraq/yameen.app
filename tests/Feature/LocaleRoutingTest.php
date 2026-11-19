<?php

it('serves Arabic at the root with rtl direction', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('صحّح وانسخ');
});

it('serves English under /en with ltr direction', function () {
    $this->get('/en')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', false)
        ->assertSee('Fix and copy');
});

it('mirrors every public route in both locales', function (string $path) {
    $this->get($path)->assertOk();
    $this->get('/en'.$path)->assertOk()->assertSee('dir="ltr"', false);
})->with([
    '/fix', '/fix/photoshop', '/fix/cinema-4d', '/pdf-fix', '/clean',
    '/fonts', '/fonts/compare', '/fonts/pairing', '/fonts/style/kufi',
    '/export', '/pricing', '/login', '/about', '/privacy', '/terms', '/contact',
]);

it('rejects unknown software and font styles', function () {
    $this->get('/fix/notepad')->assertNotFound();
    $this->get('/fonts/style/comic')->assertNotFound();
});

it('links the language switch to the same page in the other locale', function () {
    $this->get('/fix/unity')
        ->assertSee('href="'.url('/en/fix/unity').'" hreflang="en"', false);

    $this->get('/en/fix/unity')
        ->assertSee('href="'.url('/fix/unity').'" hreflang="ar"', false);
});

it('outputs hreflang alternates with x-default pointing to Arabic', function () {
    $this->get('/en/fonts')
        ->assertSee('<link rel="alternate" hreflang="ar" href="'.url('/fonts').'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/fonts').'">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/fonts').'">', false);
});

it('marks the current nav item', function () {
    $this->get('/fonts/compare')->assertSee('aria-current="page"', false);
});
