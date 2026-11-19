<?php

use App\Models\AdminAction;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => $this->actingAs($this->admin = User::factory()->admin()->create()));

it('shows the dashboard with live numbers', function () {
    User::factory()->pro()->create();
    $this->get('/admin')->assertOk()->assertSee('مشتركو Pro الآن')->assertSee('إيرادات هذا الشهر');
});

it('adds and removes Pro days from the user page', function () {
    $user = User::factory()->create();
    $this->get("/admin/users/{$user->id}")->assertOk()->assertSee($user->email);

    $this->post("/admin/users/{$user->id}/pro", ['days' => 30]);
    $this->post("/admin/users/{$user->id}/pro", ['days' => 365]);
    expect((int) round(now()->diffInDays($user->fresh()->pro_until)))->toBe(395);

    $this->post("/admin/users/{$user->id}/pro", ['days' => -400]);
    expect($user->fresh()->pro_until)->toBeNull(); // removing more than left ends Pro
});

it('sets an exact end date (end of day, Baghdad) or clears it', function () {
    $user = User::factory()->create();
    $this->post("/admin/users/{$user->id}/pro-until", ['pro_until' => '2027-03-15']);
    expect($user->fresh()->pro_until->equalTo(Carbon::parse('2027-03-15 23:59:59', 'Asia/Baghdad')))->toBeTrue();

    $this->post("/admin/users/{$user->id}/pro-until", ['pro_until' => '']);
    expect($user->fresh()->pro_until)->toBeNull();
});

it('resets credits, toggles admin and deletes users with confirmation', function () {
    $user = User::factory()->create();
    DB::table('credit_usages')->insert(['user_id' => $user->id, 'day' => \App\Services\Credits::today(), 'action' => 'fix', 'cost' => 5, 'created_at' => now()]);
    $this->post("/admin/users/{$user->id}/credits");
    expect(DB::table('credit_usages')->count())->toBe(0);

    $this->post("/admin/users/{$user->id}/admin");
    expect($user->fresh()->is_admin)->toBeTrue();
    $this->post("/admin/users/{$this->admin->id}/admin")->assertStatus(422); // not yourself

    $this->delete("/admin/users/{$user->id}", ['confirm' => 'nope@example.com'])->assertSessionHasErrors('confirm');
    $this->delete("/admin/users/{$user->id}", ['confirm' => $user->email])->assertRedirect(route('admin.users'));
    expect(User::find($user->id))->toBeNull();
});

it('confirms a pending payment manually and can mark orders failed', function () {
    $user = User::factory()->create();
    $make = fn () => Order::create(['user_id' => $user->id, 'plan' => 'monthly', 'days' => 30, 'price_usd' => 5, 'rate_used' => 1310, 'amount_iqd' => 6750, 'status' => 'pending']);
    $order = $make();
    $this->post("/admin/orders/{$order->id}/paid");
    expect($order->fresh()->status)->toBe('paid')->and($user->fresh()->isPro())->toBeTrue();
    $this->post("/admin/orders/{$order->id}/paid")->assertSessionHas('status', 'الطلب ليس بانتظار الدفع.');

    $other = $make();
    $this->post("/admin/orders/{$other->id}/failed");
    expect($other->fresh()->status)->toBe('failed');
});

it('edits plan prices and durations used by pricing and checkout', function () {
    $this->post('/admin/settings', ['plan_monthly_price' => 6, 'plan_monthly_days' => 31, 'plan_yearly_price' => 48, 'plan_yearly_days' => 366])->assertSessionHasNoErrors();
    expect(\App\Services\Payments::plan('yearly'))->toMatchArray(['price_usd' => 48.0, 'days' => 366]);
    $this->get('/pricing')->assertSee('$48')->assertSee('يعادل 8 أشهر')->assertSee('31 يوماً');
});

it('records every change in the log, visible to admins only', function () {
    $user = User::factory()->create();
    $this->post("/admin/users/{$user->id}/pro", ['days' => 7]);
    $this->post('/admin/settings', ['credits_free' => 20]);
    expect(AdminAction::pluck('action')->all())->toBe(['user.pro_days', 'settings.update']);
    $this->get('/admin/log')->assertOk()->assertSee('تعديل أيام Pro')->assertSee('تعديل الإعدادات');

    $this->actingAs(User::factory()->create())->get('/admin/log')->assertNotFound();
});
