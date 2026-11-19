<?php

use App\Models\User;

beforeEach(function () {
    @mkdir(storage_path('app/pro'), 0777, true);
    file_put_contents(storage_path('app/pro/svg.js'), 'export const toSvg = () => "";');
    file_put_contents(storage_path('app/pro/batch.js'), 'export const process = () => "";');
});

it('serves Pro modules only to Pro users', function () {
    $this->get('/pro/svg.js')->assertForbidden();
    $this->actingAs(User::factory()->create())->get('/pro/svg.js')->assertForbidden();

    $this->actingAs(User::factory()->pro()->create());
    $this->get('/pro/svg.js')->assertOk()->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
    $this->get('/pro/batch.js')->assertOk();
    $this->get('/pro/other.js')->assertNotFound();
});

it('does not expire Pro access before pro_until', function () {
    $this->actingAs(User::factory()->create(['pro_until' => now()->subMinute()]))->get('/pro/svg.js')->assertForbidden();
});

it('renders the export page', function () {
    $this->get('/export')->assertOk()->assertSee('x-data="exporter(', false)->assertSee('نزّل PNG');
});

it('shows the batch tool to Pro users and a paywall to others', function () {
    $this->get('/batch')->assertOk()->assertSee('معالجة الملفات متاحة لمشتركي Pro')->assertDontSee('batchTool');
    $this->actingAs(User::factory()->pro()->create())->get('/batch')->assertOk()->assertSee('x-data="batchTool"', false);
});
