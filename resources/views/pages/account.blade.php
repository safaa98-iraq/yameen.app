<x-layouts.app :title="__('ui.account.title')" :description="__('ui.account.title')" :noindex="true">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.account.h1')" :intro="$user->email" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8 space-y-6">
        @if (session('status'))
            <p class="rounded-panel bg-lav-100 border border-lav-200 p-4 text-small" role="status">{{ session('status') }}</p>
        @endif

        <section class="rounded-panel border border-line bg-surface p-5 md:p-6" aria-labelledby="plan-title">
            <h2 id="plan-title" class="font-semibold">{{ __('ui.account.plan') }}</h2>
            @if ($user->isPro())
                <p class="mt-2 text-h3 font-semibold text-lav-700">Pro</p>
                <p class="mt-1 text-small text-muted">{{ __('ui.account.pro_until', ['date' => $user->pro_until->timezone(config('yameen.credits_timezone'))->format('Y-m-d')]) }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ lroute('pricing') }}" class="btn btn-secondary">{{ __('ui.account.extend') }}</a>
                    <a href="{{ lroute('favorites') }}" class="btn btn-secondary">{{ __('ui.favorites.nav') }}</a>
                </div>
            @else
                <p class="mt-2 text-h3 font-semibold">{{ __('ui.account.free') }}</p>
                <p class="mt-1 text-small text-muted">{{ __('ui.account.free_note', ['count' => \App\Models\Setting::get('credits_free')]) }}</p>
                <a href="{{ lroute('pricing') }}" class="btn btn-primary mt-4">{{ __('ui.account.upgrade') }}</a>
            @endif
        </section>

        <section class="rounded-panel border border-line bg-surface p-5 md:p-6" aria-labelledby="orders-title">
            <h2 id="orders-title" class="font-semibold">{{ __('ui.account.orders') }}</h2>
            @if ($orders->isEmpty())
                <p class="mt-2 text-small text-muted">{{ __('ui.account.no_orders') }}</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-small">
                        <thead class="text-muted text-start">
                            <tr class="border-b border-line">
                                <th class="py-2 text-start font-medium">{{ __('ui.account.date') }}</th>
                                <th class="py-2 text-start font-medium">{{ __('ui.account.plan') }}</th>
                                <th class="py-2 text-start font-medium">{{ __('ui.account.amount') }}</th>
                                <th class="py-2 text-start font-medium">{{ __('ui.account.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr class="border-b border-line last:border-0">
                                    <td class="py-2 tabular-nums">{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td class="py-2">{{ __('ui.pricing.'.$order->plan) }}</td>
                                    <td class="py-2 tabular-nums">{{ number_format($order->amount_iqd) }} {{ __('ui.pricing.iqd') }}</td>
                                    <td class="py-2">{{ __('ui.account.status_'.$order->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="rounded-panel border border-line bg-surface p-5 md:p-6 flex flex-wrap items-center justify-between gap-4">
            <form method="POST" action="{{ lroute('logout') }}">
                @csrf
                <button class="btn btn-secondary">{{ __('ui.account.logout') }}</button>
            </form>
            <details class="w-full">
                <summary class="cursor-pointer text-small text-warn min-h-11 inline-flex items-center">{{ __('ui.account.delete') }}</summary>
                <form method="POST" action="{{ lroute('account.destroy') }}" class="mt-3 space-y-3">
                    @csrf
                    @method('DELETE')
                    <label for="confirm" class="block text-small">{{ __('ui.account.delete_confirm') }}</label>
                    <input id="confirm" name="confirm" type="email" required class="field h-11 px-3" dir="ltr" placeholder="{{ $user->email }}">
                    @error('confirm') <p class="text-small text-warn">{{ __('ui.account.delete_mismatch') }}</p> @enderror
                    <button class="btn border-warn text-warn bg-surface">{{ __('ui.account.delete_button') }}</button>
                </form>
            </details>
        </section>
    </div>
</x-layouts.app>
