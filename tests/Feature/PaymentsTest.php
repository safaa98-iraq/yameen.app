<?php

use App\Jobs\ProcessWaylEvent;
use App\Mail\ProReminderMail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\WaylEvent;
use App\Services\Payments;
use App\Services\Wayl\WaylClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['services.wayl' => ['base_url' => 'https://api.thewayl.com', 'token' => 'test-token', 'webhook_secret' => 'whsec', 'env' => 'test']]);
    app()->forgetInstance(WaylClient::class);
});

function pendingOrder(User $user, string $plan = 'monthly'): Order
{
    $p = Payments::plan($plan);

    return Order::create(['user_id' => $user->id, 'plan' => $plan, 'days' => $p['days'], 'price_usd' => $p['price_usd'],
        'rate_used' => 1310, 'amount_iqd' => $p['amount_iqd'], 'status' => 'pending']);
}

function signedWebhook(array $payload, string $secret = 'whsec')
{
    $raw = json_encode($payload);

    return test()->call('POST', '/webhooks/wayl', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_WAYL_SIGNATURE_256' => hash_hmac('sha256', $raw, $secret),
    ], $raw);
}

it('rounds the IQD amount up to the next 250', function () {
    expect(Payments::amountIqd(5, 1310))->toBe(6750)      // 6550 → 6750
        ->and(Payments::amountIqd(40, 1310))->toBe(52500) // 52400 → 52500
        ->and(Payments::amountIqd(5, 1300))->toBe(6500)   // exact multiple stays
        ->and(Payments::amountIqd(40, 1365))->toBe(54750); // 54600 → 54750
});

it('shows USD prices with the IQD amount from the admin rate', function () {
    Setting::put('usd_to_iqd_rate', 1365);
    $this->get('/pricing')->assertOk()->assertSee('$40')->assertSee('يُدفع 54,750 د.ع')->assertSee('يعادل 8 أشهر');
});

it('creates an order and redirects to the Wayl checkout', function () {
    Http::fake(['api.thewayl.com/api/v1/links' => Http::response(['data' => ['url' => 'https://pay.thewayl.com/x']], 201)]);
    $user = User::factory()->create();

    $this->actingAs($user)->post('/checkout', ['plan' => 'yearly'])->assertRedirect('https://pay.thewayl.com/x');

    $order = Order::sole();
    expect($order->status)->toBe('pending')->and($order->amount_iqd)->toBe(52500)->and((float) $order->rate_used)->toBe(1310.0);
    Http::assertSent(function ($request) use ($order) {
        return $request->hasHeader('X-WAYL-AUTHENTICATION', 'test-token')
            && $request['referenceId'] === $order->id
            && $request['total'] === 52500
            && $request['currency'] === 'IQD'
            && $request['lineItem'][0]['amount'] === 52500
            && $request['lineItem'][0]['type'] === 'increase'
            && $request['webhookSecret'] === 'whsec'
            && str_contains($request['redirectionUrl'], '/checkout/return/'.$order->id);
    });
});

it('rejects webhooks with a bad or missing signature', function () {
    $user = User::factory()->create();
    $order = pendingOrder($user);
    signedWebhook(['referenceId' => $order->id, 'paymentStatus' => 'Paid'], 'wrong')->assertStatus(401);
    $this->postJson('/webhooks/wayl', ['referenceId' => $order->id, 'paymentStatus' => 'Paid'])->assertStatus(401);
    expect(WaylEvent::count())->toBe(0)->and($order->fresh()->status)->toBe('pending');
});

it('stores the raw event, answers 204 and queues processing', function () {
    Queue::fake();
    $order = pendingOrder(User::factory()->create());
    signedWebhook(['referenceId' => $order->id, 'paymentStatus' => 'Paid'])->assertNoContent();

    expect(WaylEvent::sole()->reference_id)->toBe($order->id);
    Queue::assertPushed(ProcessWaylEvent::class);
    expect($order->fresh()->status)->toBe('pending'); // not activated until the job runs
});

it('activates Pro once when the webhook is processed, even if delivered twice', function () {
    $user = User::factory()->create();
    $order = pendingOrder($user);
    signedWebhook(['referenceId' => $order->id, 'paymentStatus' => 'Paid', 'code' => 'W1'])->assertNoContent();
    signedWebhook(['referenceId' => $order->id, 'paymentStatus' => 'Paid', 'code' => 'W1'])->assertNoContent();

    $user->refresh();
    expect($order->fresh()->status)->toBe('paid')
        ->and($order->fresh()->wayl_code)->toBe('W1')
        ->and($user->pro_until->diffInDays(now()->addDays(30), true))->toBeLessThan(1);
    expect(WaylEvent::pluck('result')->all())->toBe(['paid', 'already-paid']);
});

it('ignores events that are not Paid', function () {
    $order = pendingOrder(User::factory()->create());
    signedWebhook(['referenceId' => $order->id, 'paymentStatus' => 'Pending'])->assertNoContent();
    expect($order->fresh()->status)->toBe('pending')->and(WaylEvent::sole()->result)->toBe('ignored:Pending');
});

it('extends Pro from the current end date when still active', function () {
    $user = User::factory()->create(['pro_until' => now()->addDays(10)]);
    app(Payments::class)->markPaid(pendingOrder($user, 'yearly')->id);
    expect((int) round(now()->diffInDays($user->fresh()->pro_until)))->toBe(375);

    $expired = User::factory()->create(['pro_until' => now()->subDays(5)]);
    app(Payments::class)->markPaid(pendingOrder($expired)->id);
    expect((int) round(now()->diffInDays($expired->fresh()->pro_until)))->toBe(30);
});

it('never activates Pro from the redirect alone', function () {
    $user = User::factory()->create();
    $order = pendingOrder($user);
    $this->actingAs($user)->get('/checkout/return/'.$order->id)->assertOk()->assertSee('جارٍ تأكيد الدفع');
    $this->getJson('/api/orders/'.$order->id.'/status')->assertJson(['status' => 'pending']);
    expect($user->fresh()->isPro())->toBeFalse();

    $this->actingAs(User::factory()->create())->get('/checkout/return/'.$order->id)->assertNotFound();
});

it('reconciles pending orders between 30 minutes and 48 hours old', function () {
    $user = User::factory()->create();
    $recent = pendingOrder($user);
    $old = pendingOrder($user);
    Order::whereKey($old->id)->update(['created_at' => now()->subHour()]);
    Http::fake(['api.thewayl.com/api/v1/links/*' => Http::response(['data' => ['referenceId' => $old->id, 'paymentStatus' => 'Paid']])]);

    $this->artisan('wayl:reconcile')->assertSuccessful();

    expect($old->fresh()->status)->toBe('paid')->and($recent->fresh()->status)->toBe('pending');
    Http::assertSentCount(1);
});

it('requires a refund reason of 100 to 1500 characters and takes back Pro days', function () {
    Http::fake(['api.thewayl.com/api/v1/refunds' => Http::response(['ok' => true])]);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $order = pendingOrder($user, 'yearly');
    app(Payments::class)->markPaid($order->id);
    $before = $user->fresh()->pro_until;

    $this->actingAs($admin)->post("/admin/orders/{$order->id}/refund", ['amount' => 52500, 'reason' => str_repeat('a', 99)])
        ->assertSessionHasErrors('reason');
    $this->post("/admin/orders/{$order->id}/refund", ['amount' => 52500, 'reason' => str_repeat('a', 1501)])
        ->assertSessionHasErrors('reason');

    $this->post("/admin/orders/{$order->id}/refund", ['amount' => 52500, 'reason' => str_repeat('سبب ', 30)])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('refunded');
    expect((int) round($user->fresh()->pro_until->diffInDays($before)))->toBe(365);
    Http::assertSent(fn ($r) => $r['referenceId'] === $order->id && $r['amount'] === 52500 && mb_strlen($r['reason']) >= 100);
});

it('emails a reminder 3 days before Pro ends, once', function () {
    Mail::fake();
    $soon = User::factory()->create(['pro_until' => now()->addDays(3)->subHours(2)]);
    User::factory()->create(['pro_until' => now()->addDays(10)]);

    $this->artisan('pro:remind')->assertSuccessful();
    $this->artisan('pro:remind')->assertSuccessful();

    Mail::assertSent(ProReminderMail::class, 1);
    Mail::assertSent(ProReminderMail::class, fn ($m) => $m->hasTo($soon->email));
});

it('restricts the admin area to admins', function () {
    $this->actingAs(User::factory()->create())->get('/admin/orders')->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get('/admin/orders')->assertOk();
});

it('lets admins edit settings, grant Pro and make admins', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $this->actingAs($admin)->post('/admin/settings', [
        'usd_to_iqd_rate' => '1320.5', 'credits_visitor' => 5, 'credits_free' => 20, 'cost_fix' => 1,
        'cost_preview' => 1, 'cost_png' => 2, 'ads_enabled' => '1', 'adsense_client' => 'ca-pub-123',
    ])->assertSessionHasNoErrors();
    expect(Setting::get('usd_to_iqd_rate'))->toBe(1320.5)->and(Setting::get('ads_enabled'))->toBeTrue();

    $this->post("/admin/users/{$user->id}/pro", ['days' => 7]);
    expect($user->fresh()->isPro())->toBeTrue();
    $this->post("/admin/users/{$user->id}/admin");
    expect($user->fresh()->is_admin)->toBeTrue();
});

it('never logs the Wayl token or webhook secret', function () {
    $code = file_get_contents(app_path('Services/Wayl/WaylClient.php')).file_get_contents(app_path('Console/Commands/WaylReconcile.php'));
    expect($code)->not->toMatch('/Log::[a-z]+\([^;]*(token|webhookSecret)/i');
});
