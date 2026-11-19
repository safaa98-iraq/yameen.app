{{-- Calm inline panel (not a modal) shown when a charged action is refused. --}}
@php $free = \App\Models\Setting::get('credits_free'); @endphp
<div x-data="{ get time() {
        const ms = $store.credits.msUntilReset, h = Math.floor(ms / 3600000), m = Math.ceil((ms % 3600000) / 60000);
        return h ? @js(__('ui.credits_ui.hours')).replace(':h', h).replace(':m', m) : @js(__('ui.credits_ui.minutes')).replace(':m', m);
     } }"
     x-show="$store.credits.blocked" x-cloak role="status" data-limit-panel
     {{ $attributes->merge(['class' => 'rounded-panel border border-warn/30 bg-surface p-4 md:p-5']) }}>
    <template x-if="$store.credits.reason === 'pro'">
        <p class="font-semibold text-warn">{{ __('ui.credits_ui.pro_only') }}</p>
    </template>
    <template x-if="$store.credits.reason !== 'pro'">
        <div>
            <p class="font-semibold text-warn">{{ __('ui.credits_ui.limit_title') }}</p>
            <p class="mt-1 text-small text-muted" x-text="@js(__('ui.credits_ui.limit_body')).replace(':time', time)"></p>
        </div>
    </template>
    <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ lroute('pricing') }}" class="btn btn-primary">{{ __('ui.credits_ui.subscribe') }}</a>
        <a href="{{ lroute('login') }}" class="btn btn-secondary" x-show="!$store.credits.signedIn">{{ __('ui.credits_ui.signup', ['count' => $free]) }}</a>
    </div>
</div>
