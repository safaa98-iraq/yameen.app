<x-admin-layout :title="__('ui.admin.settings')">
    <div class="grid gap-6 lg:grid-cols-[minmax(0,36rem)_minmax(0,1fr)] items-start" x-data="creditCalc">
    <form method="POST" action="{{ route('admin.settings.save') }}" class="space-y-4 rounded-panel border border-line bg-surface p-5" x-on:input="read($el)">
        @csrf
        @foreach ($values as $key => $value)
            @if ($key === 'ads_enabled')
                <label class="flex items-center gap-2 min-h-11"><input type="checkbox" name="ads_enabled" value="1" @checked($value)> {{ __('ui.admin.s_'.$key) }}</label>
            @else
                <label class="block">
                    <span class="label">{{ __('ui.admin.s_'.$key) }}</span>
                    <input name="{{ $key }}" value="{{ old($key, $value) }}" class="field mt-1 h-11 px-3" dir="ltr"
                           @if (! in_array($key, ['adsense_client', 'ad_slot_tool', 'ad_slot_grid', 'ad_slot_sidebar'])) type="number" step="{{ in_array($key, ['usd_to_iqd_rate', 'plan_monthly_price', 'plan_lifetime_price']) ? '0.01' : '1' }}" @endif>
                    @error($key) <span class="text-meta text-warn">{{ $message }}</span> @enderror
                </label>
            @endif
        @endforeach
        <button class="btn btn-primary">{{ __('ui.admin.save') }}</button>
    </form>

    <section class="rounded-panel border border-line bg-surface p-5 space-y-4 lg:sticky lg:top-4" aria-labelledby="calc-title">
        <div>
            <h2 id="calc-title" class="font-semibold">{{ __('ui.admin.calc') }}</h2>
            <p class="text-meta text-muted">{{ __('ui.admin.calc_intro') }}</p>
        </div>
        <table class="w-full text-small">
            <thead class="text-muted"><tr class="border-b border-line">
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_who') }}</th>
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_credits') }}</th>
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_fix') }}</th>
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_app') }}</th>
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_preview') }}</th>
                <th class="py-2 text-start font-medium">{{ __('ui.admin.calc_png') }}</th>
            </tr></thead>
            <tbody class="tabular-nums">
                @foreach (['visitor' => 'credits_visitor', 'free' => 'credits_free'] as $who => $key)
                    <tr class="border-b border-line last:border-0">
                        <td class="py-2">{{ __('ui.admin.calc_'.$who) }}</td>
                        <td class="py-2" x-text="v.{{ $key }}"></td>
                        <td class="py-2" x-text="per(v.{{ $key }}, v.cost_fix)"></td>
                        <td class="py-2" x-text="per(v.{{ $key }}, v.cost_app)"></td>
                        <td class="py-2" x-text="per(v.{{ $key }}, v.cost_preview)"></td>
                        <td class="py-2" x-text="per(v.{{ $key }}, v.cost_png)"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <fieldset class="space-y-2">
            <legend class="label">{{ __('ui.admin.calc_try') }}</legend>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @foreach (['fix', 'app', 'preview', 'png'] as $a)
                    <label class="text-meta">{{ __('ui.admin.calc_'.$a) }}
                        <input type="number" min="0" max="999" x-model.number="use.{{ $a }}" class="field mt-1 h-11 px-3" dir="ltr">
                    </label>
                @endforeach
            </div>
            <p class="text-small" x-text="need()"></p>
            <p class="text-small font-medium" :class="verdictClass()" x-text="verdict()"></p>
            <p class="text-meta text-muted" x-show="needed() > 0" x-text="monthly()"></p>
        </fieldset>

        <p class="text-meta text-muted border-t border-line pt-3">
            {{ __('ui.admin.calc_usage', ['total' => number_format($usage['total']), 'users' => $usage['users'], 'avg' => $usage['avg']]) }}
            {{ __('ui.admin.calc_hit', ['n' => $usage['hit']]) }}
        </p>
    </section>
    </div>

    @php
        $calcText = ['need' => __('ui.admin.calc_need'), 'visitor' => __('ui.admin.calc_fits_visitor'), 'free' => __('ui.admin.calc_fits_free'), 'pro' => __('ui.admin.calc_needs_pro'), 'month' => __('ui.admin.calc_month')];
        $calcValues = $values->map(fn ($x) => is_numeric($x) ? +$x : $x);
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {
            const t = @json($calcText);
            Alpine.data('creditCalc', () => ({
                v: @json($calcValues),
                use: { fix: 5, app: 10, preview: 3, png: 2 },
                read(form) {
                    for (const k of ['credits_visitor', 'credits_free', 'cost_fix', 'cost_app', 'cost_preview', 'cost_png', 'plan_monthly_price']) {
                        const el = form.elements[k];
                        if (el) this.v[k] = Number(el.value) || 0;
                    }
                },
                per(credits, cost) { return cost > 0 ? Math.floor(credits / cost) : '∞'; },
                needed() { return this.use.fix * this.v.cost_fix + this.use.app * this.v.cost_app + this.use.preview * this.v.cost_preview + this.use.png * this.v.cost_png; },
                need() { return t.need.replace(':n', this.needed()); },
                level() { const n = this.needed(); return n <= this.v.credits_visitor ? 'visitor' : n <= this.v.credits_free ? 'free' : 'pro'; },
                verdict() { return t[this.level()]; },
                verdictClass() { return this.level() === 'pro' ? 'text-lav-700' : 'text-ink'; },
                monthly() {
                    const ops = this.use.fix + this.use.app + this.use.preview + this.use.png;
                    return t.month.replace(':usd', ops ? (this.v.plan_monthly_price / ops).toFixed(4) : '0');
                },
            }));
        });
    </script>
</x-admin-layout>
