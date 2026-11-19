<?php

use App\Models\Setting;
use App\Models\User;

it('caches public pages for guests', function () {
    $this->get('/fix')->assertOk()->assertHeader('X-Page-Cache', 'MISS');
    $this->get('/fix')->assertOk()->assertHeader('X-Page-Cache', 'HIT');
    $this->get('/en/fix')->assertHeader('X-Page-Cache', 'MISS'); // per URL
});

it('never caches for signed-in users or private pages', function () {
    $this->actingAs(User::factory()->create())->get('/fix')->assertHeaderMissing('X-Page-Cache');
    auth()->logout();
    $this->get('/login')->assertHeaderMissing('X-Page-Cache');
});

it('drops cached pages when settings change', function () {
    $this->get('/pricing');
    $this->get('/pricing')->assertHeader('X-Page-Cache', 'HIT');
    Setting::put('plan_monthly_price', 7);
    $this->get('/pricing')->assertHeader('X-Page-Cache', 'MISS')->assertSee('$7');
});

it('does not put a CSRF token in pages cached for guests', function () {
    foreach (['/', '/fix', '/pricing', '/fonts', '/export', '/batch'] as $path) {
        expect($this->get($path)->getContent())->not->toContain('name="_token"');
    }
});
