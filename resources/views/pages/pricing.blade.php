@php
    $features = __('ui.pricing.features');
    $plans = ['lifetime' => $lifetime, 'monthly' => $monthly];
@endphp
<x-layouts.app :title="__('ui.pricing.title')" :description="__('ui.pricing.description')"
               :breadcrumbs="[[__('ui.nav.pricing'), lroute('pricing')]]">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.pricing.h1')" :intro="__('ui.pricing.intro')" />
    </x-slot:hero>

    <div class="max-w-4xl mx-auto pt-8">
        @error('plan')
            <p class="mb-4 rounded-panel border border-warn/30 bg-surface p-4 text-small text-warn" role="alert">{{ $message }}</p>
        @enderror

        <div class="grid gap-4 md:grid-cols-3">
            {{-- Free --}}
            <section class="rounded-panel border border-line bg-surface p-5 md:p-6 flex flex-col" aria-labelledby="plan-free">
                <h2 id="plan-free" class="font-semibold">{{ __('ui.pricing.free') }}</h2>
                <p class="mt-3 text-h2 font-semibold tabular-nums">0</p>
                <p class="text-small text-muted">{{ __('ui.pricing.free_note', ['visitor' => $visitorCredits, 'free' => $freeCredits]) }}</p>
                <ul class="mt-5 space-y-2 text-small flex-1">
                    @foreach (__('ui.pricing.free_features') as $f)
                        <li class="flex gap-2"><span class="text-lav-700" aria-hidden="true">✓</span>{{ $f }}</li>
                    @endforeach
                </ul>
                @guest
                    <a href="{{ lroute('login') }}" class="btn btn-secondary mt-6">{{ __('ui.pricing.free_cta') }}</a>
                @endguest
            </section>

            @foreach ($plans as $key => $plan)
                <section @class([
                    'rounded-panel bg-surface p-5 md:p-6 flex flex-col',
                    'border-2 border-lav-700 md:-my-2' => $key === 'lifetime',
                    'border border-line' => $key !== 'lifetime',
                ]) aria-labelledby="plan-{{ $key }}">
                    <div class="flex items-center justify-between gap-2">
                        <h2 id="plan-{{ $key }}" class="font-semibold">Pro · {{ __('ui.pricing.'.$key) }}</h2>
                        @if ($key === 'lifetime')
                            <span class="rounded-full bg-lav-100 px-3 py-1 text-meta text-lav-800">{{ __('ui.pricing.recommended') }}</span>
                        @endif
                    </div>
                    <p class="mt-3"><span class="text-h2 font-semibold tabular-nums" dir="ltr">${{ rtrim(rtrim(number_format($plan['price_usd'], 2), '0'), '.') }}</span>
                        <span class="text-small text-muted">/ {{ $key === 'lifetime' ? __('ui.pricing.once') : trans_choice('ui.pricing.per_days', $plan['days'], ['days' => $plan['days']]) }}</span></p>
                    <p class="text-small text-muted">{{ __('ui.pricing.'.$key.'_note') }}</p>
                    <ul class="mt-5 space-y-2 text-small flex-1">
                        @foreach ($features as $f)
                            <li class="flex gap-2"><span class="text-lav-700" aria-hidden="true">✓</span>{{ $f }}</li>
                        @endforeach
                    </ul>
                    @auth
                        <form method="POST" action="{{ lroute('checkout') }}" class="mt-6">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $key }}">
                            <button @class(['btn btn-primary-lg w-full', 'btn-primary' => $key === 'lifetime', 'btn-secondary' => $key !== 'lifetime'])>{{ __('ui.pricing.buy') }}</button>
                        </form>
                    @else
                        <a href="{{ lroute('login') }}" @class(['btn btn-primary-lg mt-6', 'btn-primary' => $key === 'lifetime', 'btn-secondary' => $key !== 'lifetime'])>{{ __('ui.pricing.login_to_buy') }}</a>
                    @endauth
                </section>
            @endforeach
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 text-small text-muted">
            <p>{{ __('ui.pricing.no_renewal') }}</p>
            <p>{{ __('ui.pricing.payment_note') }}</p>
        </div>
    </div>
</x-layouts.app>
