<?php

use App\Models\Setting;
use App\Models\User;

function enableAds(): void
{
    Setting::put('ads_enabled', true);
    Setting::put('adsense_client', 'ca-pub-123');
    Setting::put('ad_slot_tool', '111');
    Setting::put('ad_slot_sidebar', '333');
}

it('shows no ads by default', function () {
    $this->get('/fix')->assertDontSee('adsbygoogle');
});

it('shows ads below the tool with reserved space and a lazy loader', function () {
    enableAds();
    $this->get('/fix')
        ->assertSee('data-ad-slot="111"', false)
        ->assertSee('min-height: 280px', false)
        ->assertSee("setTimeout(load, 3000)", false)
        ->assertDontSee('<script async src="https://pagead2', false); // never loaded eagerly
});

it('never shows ads to Pro users', function () {
    enableAds();
    $this->actingAs(User::factory()->pro()->create())->get('/fix')->assertDontSee('adsbygoogle');
});

it('puts the sidebar ad on guides only on desktop', function () {
    enableAds();
    $this->get('/blog/arabic-text-in-design-apps')->assertSee('data-ad-slot="333"', false)->assertSee('hidden xl:block', false);
});
