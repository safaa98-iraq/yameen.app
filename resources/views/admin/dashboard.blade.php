<x-admin-layout :title="__('ui.admin.dashboard')">
    <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['users', number_format($stats['users']), __('ui.admin.st_new', ['n' => $stats['new_users']])],
            ['pro', number_format($stats['pro']), __('ui.admin.st_lifetime', ['n' => $stats['lifetime']]).' · '.__('ui.admin.st_expiring', ['n' => $stats['expiring']])],
            ['revenue_month', number_format($stats['revenue_month']).' '.__('ui.pricing.iqd'), __('ui.admin.st_orders', ['n' => $stats['paid_month']])],
            ['pending', number_format($stats['pending']), __('ui.admin.st_credits', ['n' => number_format($stats['credits_today'])])],
        ] as [$key, $value, $sub])
            <div class="rounded-panel border border-line bg-surface p-4">
                <p class="text-meta text-muted">{{ __('ui.admin.st_'.$key) }}</p>
                <p class="mt-1 text-h3 font-semibold tabular-nums">{{ $value }}</p>
                <p class="mt-1 text-meta text-muted">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section>
            <div class="flex items-baseline justify-between"><h2 class="font-semibold">{{ __('ui.admin.recent_orders') }}</h2><a href="{{ route('admin.orders') }}" class="text-small">{{ __('ui.admin.all') }}</a></div>
            <ul class="mt-3 divide-y divide-line rounded-panel border border-line bg-surface text-small">
                @forelse ($recentOrders as $o)
                    <li class="flex items-center justify-between gap-3 p-3">
                        <a href="{{ $o->user ? route('admin.users.show', $o->user) : '#' }}" dir="ltr" class="truncate">{{ $o->user?->email }}</a>
                        <span class="tabular-nums shrink-0">{{ number_format($o->amount_iqd) }} · {{ __('ui.account.status_'.$o->status) }}</span>
                    </li>
                @empty
                    <li class="p-3 text-muted">{{ __('ui.account.no_orders') }}</li>
                @endforelse
            </ul>
        </section>
        <section>
            <div class="flex items-baseline justify-between"><h2 class="font-semibold">{{ __('ui.admin.recent_actions') }}</h2><a href="{{ route('admin.log') }}" class="text-small">{{ __('ui.admin.all') }}</a></div>
            <ul class="mt-3 divide-y divide-line rounded-panel border border-line bg-surface text-small">
                @forelse ($recentActions as $a)
                    <li class="flex items-center justify-between gap-3 p-3">
                        <span><span class="font-medium">{{ __('ui.admin.a_'.str_replace('.', '_', $a->action)) }}</span> <span class="text-muted" dir="ltr">{{ $a->subject }}</span></span>
                        <span class="text-meta text-muted tabular-nums shrink-0">{{ $a->created_at->format('m-d H:i') }}</span>
                    </li>
                @empty
                    <li class="p-3 text-muted">{{ __('ui.admin.no_actions') }}</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-admin-layout>
