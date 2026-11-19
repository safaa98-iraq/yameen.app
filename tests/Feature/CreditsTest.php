<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;

function consume(string $action, ?string $hash = null, array $cookies = [])
{
    return test()->withCookies($cookies)->postJson('/api/credits/consume', array_filter(['action' => $action, 'text_hash' => $hash]));
}

$h = fn (string $s) => hash('sha256', $s);

it('gives visitors 8 credits and sets a visitor cookie', function () {
    $this->getJson('/api/credits')
        ->assertOk()
        ->assertJson(['remaining' => 8, 'limit' => 8, 'pro' => false])
        ->assertCookie('yv');
});

it('charges once per distinct text for fixes and previews', function () use ($h) {
    consume('fix', $h('a'))->assertJson(['ok' => true, 'remaining' => 7]);
    consume('fix', $h('a'))->assertJson(['ok' => true, 'remaining' => 7]);
    consume('preview', $h('a'))->assertJson(['ok' => true, 'remaining' => 6]);
    $this->travel(2)->days();
    consume('fix', $h('a'))->assertJson(['ok' => true, 'remaining' => 6]); // still the same text
    consume('png')->assertJson(['ok' => true, 'remaining' => 4]); // PNG costs 2, never deduplicated
});

it('refuses when the limit is reached', function () use ($h) {
    foreach (range(1, 8) as $i) {
        consume('fix', $h("t$i"))->assertJson(['ok' => true]);
    }
    consume('fix', $h('t9'))->assertJson(['ok' => false, 'reason' => 'limit', 'remaining' => 0]);
});

it('counts by IP even when the visitor clears the cookie', function () use ($h) {
    foreach (range(1, 8) as $i) {
        consume('fix', $h("t$i"), ['yv' => 'first-cookie']);
    }
    consume('fix', $h('t9'), ['yv' => 'second-cookie'])->assertJson(['ok' => false]);
});

it('never stores raw IP addresses', function () use ($h) {
    consume('fix', $h('a'));
    $row = DB::table('credit_usages')->first();
    expect($row->ip_key)->toHaveLength(64)->not->toContain('127.0.0.1');
});

it('gives signed-in users 15 credits and Pro users unlimited', function () use ($h) {
    $this->actingAs(User::factory()->create());
    $this->getJson('/api/credits')->assertJson(['limit' => 15, 'remaining' => 15, 'signed_in' => true]);

    $this->actingAs(User::factory()->pro()->create());
    $this->getJson('/api/credits')->assertJson(['limit' => null, 'pro' => true]);
    consume('png')->assertJson(['ok' => true]);
    expect(DB::table('credit_usages')->count())->toBe(0);
});

it('keeps SVG export and batch for Pro users', function () {
    consume('svg')->assertJson(['ok' => false, 'reason' => 'pro']);
    $this->actingAs(User::factory()->pro()->create());
    consume('batch')->assertJson(['ok' => true]);
});

it('does not renew: the balance stays spent the next day and the next month', function () use ($h) {
    foreach (range(1, 8) as $i) {
        consume('fix', $h("t$i"));
    }
    consume('fix', $h('x'))->assertJson(['ok' => false, 'remaining' => 0])->assertJsonMissingPath('resets_at');

    $this->travel(1)->days();
    consume('fix', $h('x'))->assertJson(['ok' => false, 'remaining' => 0]);
    $this->travel(40)->days();
    $this->getJson('/api/credits')->assertJson(['remaining' => 0]);
});

it('charges copying for an app on every click, even for the same text', function () use ($h) {
    consume('app', $h('a'))->assertJson(['ok' => true, 'remaining' => 7]);
    consume('app', $h('a'))->assertJson(['ok' => true, 'remaining' => 6]);
    consume('app', $h('a'))->assertJson(['ok' => true, 'remaining' => 5]);
    Setting::put('cost_app', 2);
    consume('app', $h('a'))->assertJson(['ok' => true, 'remaining' => 3]);
});

it('reads limits and costs from admin settings', function () use ($h) {
    Setting::put('credits_visitor', 3);
    Setting::put('cost_png', 3);
    $this->getJson('/api/credits')->assertJson(['limit' => 3]);
    consume('png')->assertJson(['ok' => true, 'remaining' => 0]);
});

it('validates the action and hash', function () {
    $this->postJson('/api/credits/consume', ['action' => 'steal'])->assertStatus(422);
    $this->postJson('/api/credits/consume', ['action' => 'fix', 'text_hash' => 'nope'])->assertStatus(422);
});
