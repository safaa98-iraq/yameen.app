<x-layouts.app :title="__('ui.checkout.title')" :description="__('ui.checkout.title')" :noindex="true">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.checkout.h1')" />
    </x-slot:hero>

    <div class="max-w-md mx-auto pt-8"
         x-data="{
            status: @js($order->status), waited: 0, timer: null,
            init() { if (this.status === 'pending') this.timer = setInterval(() => this.poll(), 3000) },
            async poll() {
                this.waited += 3;
                try {
                    const r = await fetch(@js(route('api.orders.status', $order)), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (r.ok) this.status = (await r.json()).status;
                } catch {}
                if (this.status !== 'pending' || this.waited >= 180) clearInterval(this.timer);
            },
         }">
        <div class="rounded-panel border border-line bg-surface p-6 text-center" role="status" aria-live="polite">
            <template x-if="status === 'pending'">
                <div>
                    <p class="font-semibold">{{ __('ui.checkout.confirming') }}</p>
                    <p class="mt-2 text-small text-muted" x-show="waited < 60">{{ __('ui.checkout.wait') }}</p>
                    <p class="mt-2 text-small text-muted" x-show="waited >= 60" x-cloak>{{ __('ui.checkout.slow') }}</p>
                </div>
            </template>
            <template x-if="status === 'paid'">
                <div>
                    <p class="text-h3 font-semibold text-lav-700">{{ __('ui.checkout.paid') }}</p>
                    <p class="mt-2 text-small text-muted">{{ __('ui.checkout.paid_body') }}</p>
                    <a href="{{ lroute('home') }}" class="btn btn-primary mt-5">{{ __('ui.checkout.start') }}</a>
                </div>
            </template>
            <template x-if="status !== 'pending' && status !== 'paid'">
                <div>
                    <p class="font-semibold text-warn">{{ __('ui.checkout.failed') }}</p>
                    <a href="{{ lroute('pricing') }}" class="btn btn-secondary mt-5">{{ __('ui.checkout.retry') }}</a>
                </div>
            </template>
        </div>
        <p class="mt-4 text-center text-meta text-muted">{{ __('ui.checkout.order', ['id' => $order->id]) }}</p>
    </div>
</x-layouts.app>
