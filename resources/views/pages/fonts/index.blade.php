@php
    $styleName = $activeStyle ? __('ui.styles.'.$activeStyle) : null;
    $title = $activeStyle ? __('ui.fonts.style_title', ['style' => $styleName]) : __('ui.fonts.title');
    $h1 = $activeStyle ? __('ui.fonts.style_h1', ['style' => $styleName]) : __('ui.fonts.h1');
    $clientFonts = $fonts->map(fn ($f) => ['slug' => $f->slug, 'name' => $f->name, 'style' => $f->style, 'apps' => $f->presentation_forms, 'diacritics' => $f->diacritics])->values();
@endphp
<x-layouts.app :title="$title" :description="__('ui.fonts.description', ['count' => $fonts->count()])">
    <x-slot:hero>
        <x-page-hero :h1="$h1" :intro="__('ui.fonts.intro')" />
    </x-slot:hero>

    <div class="pt-8"></div>

    <div x-data="fontGrid({ style: @js($activeStyle ?? ''), sample: @js(config('yameen.fonts.default_sample.'.app()->getLocale())), fonts: @js($clientFonts) })">
        <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 grid gap-4 md:grid-cols-[2fr_1fr_1fr]" aria-label="{{ __('ui.fonts.filters') }}">
            <x-tool.input id="fonts-text" :label="__('ui.fonts.specimen_label')" />
            <div>
                <label for="fonts-size" class="label">{{ __('ui.fonts.size') }} <span class="tabular-nums" x-text="size + ' px'">40 px</span></label>
                <input id="fonts-size" type="range" min="20" max="96" step="2" x-model.number="size" class="mt-4 w-full accent-[var(--lav-700)] h-11">
            </div>
            <div>
                <label for="fonts-weight" class="label">{{ __('ui.fonts.weight') }}</label>
                <select id="fonts-weight" x-model.number="weight" class="field mt-2 h-11 px-3 text-small">
                    @foreach ([200, 300, 400, 500, 600, 700, 800, 900] as $w)
                        <option value="{{ $w }}" @selected($w === 400)>{{ $w }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 grid gap-3 md:grid-cols-[1fr_auto] md:items-end">
                <fieldset>
                    <legend class="label">{{ __('ui.fonts.filters') }}</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ lroute('fonts') }}" class="chip no-underline" @if (! $activeStyle) aria-pressed="true" aria-current="page" @endif>{{ __('ui.fonts.all_styles') }}</a>
                        @foreach (config('yameen.font_styles') as $s)
                            <a href="{{ lroute('fonts.style', ['style' => $s]) }}" class="chip no-underline"
                               @if ($activeStyle === $s) aria-pressed="true" aria-current="page" @endif>{{ __('ui.styles.'.$s) }}</a>
                        @endforeach
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="apps"> {{ __('ui.fonts.only_apps') }}</label>
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="diacritics"> {{ __('ui.fonts.only_diacritics') }}</label>
                    </div>
                </fieldset>
                <div>
                    <label for="fonts-search" class="sr-only">{{ __('ui.fonts.search') }}</label>
                    <input id="fonts-search" type="search" x-model="query" placeholder="{{ __('ui.fonts.search') }}" class="field h-11 px-3 text-small md:w-64">
                </div>
            </div>
        </section>

        <p class="mt-6 text-meta text-muted tabular-nums" aria-live="polite">
            <span x-text="@js(__('ui.fonts.count', ['count' => '__N__'])).replace('__N__', visibleCount)">{{ __('ui.fonts.count', ['count' => $fonts->count()]) }}</span>
        </p>

        @if ($fonts->isEmpty())
            <p class="mt-6 text-muted">{{ __('ui.fonts.empty_catalog') }}</p>
        @else
            <div class="mt-3 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($fonts as $font)
                    <x-font-card :font="$font" />
                @endforeach
            </div>
            <p x-show="visibleCount === 0" x-cloak class="mt-6 text-muted">{{ __('ui.fonts.no_results') }}</p>
        @endif
    </div>

    <x-related-tools except="fonts" />
</x-layouts.app>
