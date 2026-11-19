@php $sample = config('yameen.fonts.default_sample.'.app()->getLocale()); @endphp
<x-layouts.app :title="__('ui.fonts.compare_title')" :description="__('ui.fonts.compare_description')"
               :breadcrumbs="[[__('ui.pages.fonts'), lroute('fonts')], [__('ui.pages.fonts-compare'), lroute('fonts.compare')]]">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.fonts.compare_h1')" />
    </x-slot:hero>

    <div class="pt-8"></div>

    @if ($a && $b)
    <div x-data="fontCompare(@js($fonts->map->toClient()->values()), @js($a->slug), @js($b->slug), @js($sample))">
        <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 grid gap-4 md:grid-cols-[2fr_1fr_1fr]">
            <x-tool.input id="compare-text" :label="__('ui.fonts.specimen_label')" />
            <div>
                <label for="compare-size" class="label">{{ __('ui.fonts.size') }} <span class="tabular-nums" x-text="size + ' px'">48 px</span></label>
                <input id="compare-size" type="range" min="20" max="120" step="2" x-model.number="size" class="mt-4 w-full accent-[var(--lav-700)] h-11">
            </div>
            <div>
                <label for="compare-weight" class="label">{{ __('ui.fonts.weight') }}</label>
                <select id="compare-weight" x-model.number="weight" class="field mt-2 h-11 px-3 text-small">
                    @foreach ([300, 400, 500, 700, 900] as $w)
                        <option value="{{ $w }}" @selected($w === 400)>{{ $w }}</option>
                    @endforeach
                </select>
            </div>
        </section>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach (['a' => __('ui.fonts.font_a'), 'b' => __('ui.fonts.font_b')] as $key => $label)
                <section class="rounded-panel bg-surface border border-line p-4 md:p-6 flex flex-col gap-4 min-w-0">
                    <div>
                        <label for="compare-{{ $key }}" class="label">{{ $label }}</label>
                        <select id="compare-{{ $key }}" x-model="{{ $key }}" class="field mt-2 h-11 px-3 text-small" dir="ltr">
                            @foreach ($fonts as $f)
                                <option value="{{ $f->slug }}" @selected(${$key}->is($f))>{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="leading-normal break-words min-h-40" dir="auto" :style="styleFor({{ $key }})" x-text="text">{{ $sample }}</p>
                </section>
            @endforeach
        </div>
    </div>
    @else
        <p class="text-muted">{{ __('ui.fonts.empty_catalog') }}</p>
    @endif

    <x-related-tools except="fonts" />
</x-layouts.app>
