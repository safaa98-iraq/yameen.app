@php $tz = config('yameen.credits_timezone'); @endphp
<x-admin-layout :title="$user->email">
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-panel border border-line bg-surface p-5 space-y-4" aria-labelledby="sub-title">
            <h2 id="sub-title" class="font-semibold">{{ __('ui.admin.subscription') }}</h2>
            <p class="text-small">
                @if ($user->pro_lifetime)
                    <span class="font-semibold text-lav-700">Pro</span> · {{ __('ui.pricing.lifetime') }}
                @elseif ($user->isPro())
                    <span class="font-semibold text-lav-700">Pro</span> · {{ __('ui.account.pro_until', ['date' => $user->pro_until->timezone($tz)->format('Y-m-d H:i')]) }}
                    <span class="text-muted">({{ __('ui.admin.days_left', ['n' => (int) ceil(now()->diffInDays($user->pro_until))]) }})</span>
                @else
                    {{ __('ui.account.free') }}@if ($user->pro_until) · <span class="text-muted">{{ __('ui.admin.expired_on', ['date' => $user->pro_until->timezone($tz)->format('Y-m-d')]) }}</span>@endif
                @endif
            </p>

            <form method="POST" action="{{ route('admin.users.pro', $user) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <label class="block text-meta">{{ __('ui.admin.add_days') }}
                    <input name="days" type="number" min="-3650" max="3650" value="30" class="field mt-1 h-11 w-28 px-3" dir="ltr">
                </label>
                <button class="btn btn-secondary">{{ __('ui.admin.apply') }}</button>
                <span class="text-meta text-muted w-full">{{ __('ui.admin.add_days_hint') }}</span>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach ([30 => __('ui.admin.plus_month'), 365 => __('ui.admin.plus_year')] as $d => $label)
                    <form method="POST" action="{{ route('admin.users.pro', $user) }}">@csrf<input type="hidden" name="days" value="{{ $d }}"><button class="btn btn-secondary">{{ $label }}</button></form>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.users.pro_until', $user) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <label class="block text-meta">{{ __('ui.admin.exact_date') }}
                    <input name="pro_until" type="date" value="{{ $user->pro_until?->timezone($tz)->format('Y-m-d') }}" class="field mt-1 h-11 px-3" dir="ltr">
                </label>
                <button class="btn btn-secondary">{{ __('ui.admin.save') }}</button>
            </form>

            <form method="POST" action="{{ route('admin.users.lifetime', $user) }}">@csrf<button class="btn btn-secondary">{{ $user->pro_lifetime ? __('ui.admin.lifetime_off') : __('ui.admin.lifetime_on') }}</button></form>

            @if ($user->pro_until || $user->pro_lifetime)
                <form method="POST" action="{{ route('admin.users.revoke', $user) }}">@csrf<button class="btn btn-secondary text-warn">{{ __('ui.admin.revoke') }}</button></form>
            @endif
        </section>

        <section class="rounded-panel border border-line bg-surface p-5 space-y-4">
            <h2 class="font-semibold">{{ __('ui.admin.account') }}</h2>
            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-small">
                <dt class="text-muted">{{ __('ui.admin.joined') }}</dt><dd class="tabular-nums">{{ $user->created_at->format('Y-m-d') }}</dd>
                <dt class="text-muted">Google</dt><dd>{{ $user->google_id ? '✓' : '—' }}</dd>
                <dt class="text-muted">{{ __('ui.admin.credits_used') }}</dt><dd class="tabular-nums">{{ $user->isPro() ? '—' : __('ui.admin.credits_of', ['used' => $creditsUsed, 'limit' => \App\Models\Setting::get('credits_free')]) }}</dd>
                <dt class="text-muted">Admin</dt><dd>{{ $user->is_admin ? '✓' : '—' }}</dd>
            </dl>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.users.credits', $user) }}">@csrf<button class="btn btn-secondary">{{ __('ui.admin.reset_credits') }}</button></form>
                @unless ($user->is(auth()->user()))
                    <form method="POST" action="{{ route('admin.users.admin', $user) }}">@csrf<button class="btn btn-secondary">{{ $user->is_admin ? __('ui.admin.remove_admin') : __('ui.admin.make_admin') }}</button></form>
                @endunless
            </div>
            @unless ($user->is(auth()->user()))
                <details>
                    <summary class="cursor-pointer text-small text-warn min-h-11 inline-flex items-center">{{ __('ui.admin.delete_user') }}</summary>
                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="mt-2 space-y-2">
                        @csrf @method('DELETE')
                        <label class="block text-meta">{{ __('ui.admin.type_email') }}
                            <input name="confirm" type="email" required class="field mt-1 h-11 px-3" dir="ltr" placeholder="{{ $user->email }}">
                        </label>
                        <button class="btn border-warn text-warn bg-surface">{{ __('ui.admin.delete_user') }}</button>
                    </form>
                </details>
            @endunless
        </section>
    </div>

    <section class="mt-6">
        <h2 class="font-semibold">{{ __('ui.account.orders') }}</h2>
        @include('admin.partials.orders-table', ['orders' => $orders])
    </section>

    @if ($actions->isNotEmpty())
        <section class="mt-6">
            <h2 class="font-semibold">{{ __('ui.admin.log') }}</h2>
            @include('admin.partials.log-list', ['actions' => $actions])
        </section>
    @endif
</x-admin-layout>
