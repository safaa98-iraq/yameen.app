<?php

use App\Models\User;

it('hides demo accounts unless DEMO_MODE is on', function () {
    config(['yameen.demo' => false]);
    $this->get('/demo')->assertNotFound();
    $this->post('/demo/admin')->assertNotFound();
    $this->get('/login')->assertDontSee('/demo');
    expect(User::count())->toBe(0);
});

it('signs in as each demo role with the right access', function () {
    config(['yameen.demo' => true]);
    $this->get('/demo')->assertOk()->assertSee('admin@demo.yameen.app');
    $this->get('/login')->assertSee('جرّب بحساب تجريبي');

    $this->post('/demo/admin')->assertRedirect(route('admin.dashboard'));
    expect(auth()->user()->is_admin)->toBeTrue();
    $this->get('/admin')->assertOk();
    auth()->logout();

    $this->post('/demo/free')->assertRedirect();
    expect(auth()->user()->isPro())->toBeFalse()->and(auth()->user()->is_admin)->toBeFalse();
    $this->get('/admin')->assertNotFound();
    auth()->logout();

    $this->post('/demo/monthly');
    expect(auth()->user()->isPro())->toBeTrue()->and(auth()->user()->pro_lifetime)->toBeFalse();
    auth()->logout();

    $this->post('/demo/lifetime');
    expect(auth()->user()->pro_lifetime)->toBeTrue();
    $this->post('/demo/nobody')->assertNotFound();
});

it('creates the demo accounts from the command line', function () {
    $this->artisan('demo:accounts')->assertSuccessful();
    expect(User::where('email', 'like', '%@demo.yameen.app')->count())->toBe(4);
});

it('shows the credit calculator with today\'s usage', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/admin/settings')
        ->assertOk()->assertSee('حاسبة الرصيد')->assertSee('creditCalc', false)->assertSee('استهلاك اليوم');
});
