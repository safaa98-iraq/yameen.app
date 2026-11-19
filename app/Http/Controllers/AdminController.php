<?php

namespace App\Http\Controllers;

use App\Models\AdminAction;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Simple admin tables: orders (with refunds), users, settings. */
class AdminController extends Controller
{
    public const SETTINGS = [
        'usd_to_iqd_rate' => ['numeric', 'min:1'],
        'credits_visitor' => ['integer', 'min:0', 'max:1000'],
        'credits_free' => ['integer', 'min:0', 'max:1000'],
        'cost_fix' => ['integer', 'min:0', 'max:100'],
        'cost_app' => ['integer', 'min:0', 'max:100'],
        'cost_preview' => ['integer', 'min:0', 'max:100'],
        'cost_png' => ['integer', 'min:0', 'max:100'],
        'ads_enabled' => ['boolean'],
        'adsense_client' => ['nullable', 'string', 'max:64', 'regex:/^(ca-pub-\d+)?$/'],
        'ad_slot_tool' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        'ad_slot_grid' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        'ad_slot_sidebar' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
        'plan_monthly_price' => ['numeric', 'min:0.5', 'max:1000'],
        'plan_monthly_days' => ['integer', 'min:1', 'max:3650'],
        'plan_lifetime_price' => ['numeric', 'min:0.5', 'max:10000'],
    ];

    public function dashboard(): View
    {
        $tz = config('yameen.credits_timezone');
        $monthStart = Carbon::now($tz)->startOfMonth()->utc();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'new_users' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'pro' => User::pro()->count(),
                'lifetime' => User::where('pro_lifetime', true)->count(),
                'expiring' => User::where('pro_lifetime', false)->whereBetween('pro_until', [now(), now()->addDays(7)])->count(),
                'paid_month' => Order::where('status', 'paid')->where('paid_at', '>=', $monthStart)->count(),
                'revenue_month' => (int) Order::where('status', 'paid')->where('paid_at', '>=', $monthStart)->sum(DB::raw('amount_iqd - refunded_iqd')),
                'pending' => Order::where('status', 'pending')->count(),
                'credits_today' => (int) DB::table('credit_usages')->where('day', \App\Services\Credits::today())->sum('cost'),
            ],
            'recentOrders' => Order::with('user')->latest()->limit(8)->get(),
            'recentActions' => AdminAction::with('admin')->latest('id')->limit(8)->get(),
        ]);
    }

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

        AdminAction::record('order.refund', 'order:'.$order->id, ['amount_iqd' => (int) $data['amount']]);

        return back()->with('status', __('ui.admin.refunded'));
    }

    /** Confirm a payment by hand (e.g. bank transfer): same logic as the Wayl webhook. */
    public function markPaid(Order $order, Payments $payments): RedirectResponse
    {
        $result = $payments->markPaid($order->id);
        AdminAction::record('order.mark_paid', 'order:'.$order->id, ['result' => $result]);

        return back()->with('status', $result === 'paid' ? __('ui.admin.marked_paid') : __('ui.admin.not_pending'));
    }

    public function markFailed(Order $order): RedirectResponse
    {
        abort_unless($order->isPending(), 422);
        $order->update(['status' => 'failed']);
        AdminAction::record('order.mark_failed', 'order:'.$order->id);

        return back()->with('status', __('ui.admin.saved'));
    }

    public function users(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $filter = $request->query('filter');
        $users = User::query()
            ->when($q !== '', fn ($w) => $w->where('email', 'like', '%'.$q.'%'))
            ->when($filter === 'pro', fn ($w) => $w->pro())
            ->when($filter === 'admins', fn ($w) => $w->where('is_admin', true))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.users', ['users' => $users, 'q' => $q, 'filter' => $filter]);
    }

    public function user(User $user): View
    {
        return view('admin.user', [
            'user' => $user,
            'orders' => $user->orders()->latest()->get(),
            'creditsUsed' => (int) DB::table('credit_usages')->where('user_id', $user->id)->sum('cost'),
            'actions' => AdminAction::with('admin')->where('subject', 'user:'.$user->id)->latest('id')->limit(20)->get(),
        ]);
    }

    /** Add days (positive or negative) to Pro, counted from the current end date if active. */
    public function grantPro(Request $request, User $user): RedirectResponse
    {
        $days = (int) $request->validate(['days' => ['required', 'integer', 'min:-3650', 'max:3650', 'not_in:0']])['days'];
        $from = $user->isPro() ? $user->pro_until : now();
        $until = $from->copy()->addDays($days);
        $user->forceFill(['pro_until' => $until->isFuture() ? $until : null])->save();
        AdminAction::record('user.pro_days', 'user:'.$user->id, ['days' => $days, 'pro_until' => $user->pro_until?->toDateString()]);

        return back()->with('status', __('ui.admin.saved'));
    }

    /** Set an exact end date (end of that day, Baghdad time), or clear it. */
    public function setProUntil(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['pro_until' => ['nullable', 'date']]);
        $until = filled($data['pro_until'] ?? null)
            ? Carbon::parse($data['pro_until'], config('yameen.credits_timezone'))->endOfDay()->utc()
            : null;
        $user->forceFill(['pro_until' => $until])->save();
        AdminAction::record('user.pro_until', 'user:'.$user->id, ['pro_until' => $until?->toIso8601String()]);

        return back()->with('status', __('ui.admin.saved'));
    }

    /** Turn lifetime Pro on or off by hand. */
    public function toggleLifetime(User $user): RedirectResponse
    {
        $user->forceFill(['pro_lifetime' => ! $user->pro_lifetime])->save();
        AdminAction::record($user->pro_lifetime ? 'user.lifetime_on' : 'user.lifetime_off', 'user:'.$user->id);

        return back()->with('status', __('ui.admin.saved'));
    }

    public function revokePro(User $user): RedirectResponse
    {
        $user->forceFill(['pro_until' => null, 'pro_lifetime' => false])->save();
        AdminAction::record('user.pro_revoke', 'user:'.$user->id);

        return back()->with('status', __('ui.admin.saved'));
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422); // never remove your own access by accident
        $user->forceFill(['is_admin' => ! $user->is_admin])->save();
        AdminAction::record($user->is_admin ? 'user.admin_on' : 'user.admin_off', 'user:'.$user->id);

        return back()->with('status', __('ui.admin.saved'));
    }

    public function resetCredits(User $user): RedirectResponse
    {
        DB::table('credit_usages')->where('user_id', $user->id)->delete(); // a full balance again
        AdminAction::record('user.credits_reset', 'user:'.$user->id);

        return back()->with('status', __('ui.admin.saved'));
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422);
        $request->validate(['confirm' => ['required', 'in:'.$user->email]]);
        AdminAction::record('user.delete', 'user:'.$user->id, ['email' => $user->email]);
        $user->delete();

        return redirect()->route('admin.users')->with('status', __('ui.admin.deleted'));
    }

    public function log(): View
    {
        return view('admin.log', ['actions' => AdminAction::with('admin')->latest('id')->paginate(50)]);
    }

    public function settings(): View
    {
        $values = collect(self::SETTINGS)->keys()->mapWithKeys(fn ($k) => [$k => Setting::get($k)]);

        // Today's usage per user or visitor, for the credit calculator.
        $rows = DB::table('credit_usages')->where('day', \App\Services\Credits::today())
            ->selectRaw('user_id, cookie_key, SUM(cost) as total')->groupBy('user_id', 'cookie_key')->get();
        $hit = $rows->filter(fn ($r) => $r->total >= (int) Setting::get($r->user_id ? 'credits_free' : 'credits_visitor'))->count();

        return view('admin.settings', ['values' => $values, 'usage' => [
            'total' => (int) $rows->sum('total'),
            'users' => $rows->count(),
            'avg' => $rows->count() ? round($rows->avg('total'), 1) : 0,
            'hit' => $hit,
        ]]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $request->merge(['ads_enabled' => $request->boolean('ads_enabled')]);
        $data = $request->validate(collect(self::SETTINGS)->map(fn ($rules) => array_merge(['sometimes'], $rules))->all());
        foreach ($data as $key => $value) {
            $value = match ($key) {
                'usd_to_iqd_rate', 'plan_monthly_price', 'plan_lifetime_price' => (float) $value,
                'ads_enabled' => (bool) $value,
                'adsense_client', 'ad_slot_tool', 'ad_slot_grid', 'ad_slot_sidebar' => (string) $value,
                default => (int) $value,
            };
            Setting::put($key, $value);
        }
        AdminAction::record('settings.update', 'settings', $data);

        return back()->with('status', __('ui.admin.saved'));
    }
}
