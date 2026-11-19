<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Simple admin tables: orders (with refunds), users, settings. */
class AdminController extends Controller
{
    public const SETTINGS = [
        'usd_to_iqd_rate' => ['numeric', 'min:1'],
        'credits_visitor' => ['integer', 'min:0', 'max:1000'],
        'credits_free' => ['integer', 'min:0', 'max:1000'],
        'cost_fix' => ['integer', 'min:0', 'max:100'],
        'cost_preview' => ['integer', 'min:0', 'max:100'],
        'cost_png' => ['integer', 'min:0', 'max:100'],
        'ads_enabled' => ['boolean'],
        'adsense_client' => ['nullable', 'string', 'max:64', 'regex:/^(ca-pub-\d+)?$/'],
        'ad_slot_tool' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        'ad_slot_grid' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        'ad_slot_sidebar' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
    ];

    public function orders(Request $request): View
    {
        $status = $request->query('status');
        $orders = Order::with('user')
            ->when(in_array($status, ['pending', 'paid', 'failed', 'refunded'], true), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.orders', ['orders' => $orders, 'status' => $status]);
    }

    public function refund(Request $request, Order $order, Payments $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:'.($order->amount_iqd - $order->refunded_iqd)],
            'reason' => ['required', 'string', 'min:100', 'max:1500'],
        ]);

        try {
            $payments->refund($order, (int) $data['amount'], $data['reason'], $request->user());
        } catch (\Throwable $e) {
            return back()->withErrors(['refund_'.$order->id => $e->getMessage()])->withInput();
        }

        return back()->with('status', __('ui.admin.refunded'));
    }

    public function users(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $users = User::query()
            ->when($q !== '', fn ($w) => $w->where('email', 'like', '%'.$q.'%'))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.users', ['users' => $users, 'q' => $q]);
    }

    public function grantPro(Request $request, User $user): RedirectResponse
    {
        $days = (int) $request->validate(['days' => ['required', 'integer', 'min:1', 'max:3650']])['days'];
        $from = $user->isPro() ? $user->pro_until : now();
        $user->forceFill(['pro_until' => $from->copy()->addDays($days)])->save();

        return back()->with('status', __('ui.admin.saved'));
    }

    public function revokePro(User $user): RedirectResponse
    {
        $user->forceFill(['pro_until' => null])->save();

        return back()->with('status', __('ui.admin.saved'));
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422); // never remove your own access by accident
        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        return back()->with('status', __('ui.admin.saved'));
    }

    public function settings(): View
    {
        $values = collect(self::SETTINGS)->keys()->mapWithKeys(fn ($k) => [$k => Setting::get($k)]);

        return view('admin.settings', ['values' => $values]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $request->merge(['ads_enabled' => $request->boolean('ads_enabled')]);
        $data = $request->validate(collect(self::SETTINGS)->map(fn ($rules) => array_merge(['sometimes'], $rules))->all());
        foreach ($data as $key => $value) {
            $value = match ($key) {
                'usd_to_iqd_rate' => (float) $value,
                'ads_enabled' => (bool) $value,
                'adsense_client', 'ad_slot_tool', 'ad_slot_grid', 'ad_slot_sidebar' => (string) $value,
                default => (int) $value,
            };
            Setting::put($key, $value);
        }

        return back()->with('status', __('ui.admin.saved'));
    }
}
