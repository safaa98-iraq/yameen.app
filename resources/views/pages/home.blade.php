@inject('catalog', 'App\Support\FontCatalog')
@php
    $fonts = $catalog->forApps();
    $styles = $fonts->pluck('style')->unique()->sortBy(fn ($s) => array_search($s, config('yameen.font_styles')))->values();
    $sample = config('yameen.fonts.default_sample.'.app()->getLocale());
@endphp
<x-layouts.app>
    <x-slot:hero>
        <x-hero-input :h1="__('ui.home.h1')" :lead="__('ui.home.lead')" />
    </x-slot:hero>

    {{-- 1. Your text in every font --}}
    <section class="pt-10" aria-labelledby="pick-font"
             x-data="fontGrid({ sample: @js($sample), fonts: @js($fonts->map(fn ($f) => ['slug' => $f->slug, 'name' => $f->name, 'style' => $f->style, 'apps' => true, 'diacritics' => $f->diacritics])->values()) })">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="pick-font" class="text-h3 font-semibold">{{ __('ui.home.pick_font') }}</h2>
                <p class="mt-1 text-small text-muted">{{ __('ui.home.pick_font_hint') }}</p>
            </div>
            <a href="{{ lroute('fonts') }}" class="text-small font-medium">{{ __('ui.home.all_fonts') }} ←</a>
        </div>

        <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="{{ __('ui.fonts.filters') }}">
            <button type="button" class="chip" :aria-pressed="(style === '').toString()" @click="style = ''">{{ __('ui.fonts.all_styles') }}</button>
            @foreach ($styles as $s)
                <button type="button" class="chip" :aria-pressed="(style === @js($s)).toString()" @click="style = @js($s)">{{ __('ui.styles.'.$s) }}</button>
            @endforeach
        </div>

        @if ($fonts->isEmpty())
            <p class="mt-6 text-muted">{{ __('ui.fonts.empty_catalog') }}</p>
        @else
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($fonts as $font)
                    <x-font-card :font="$font" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- 2. How it works --}}
    <section class="mt-18" aria-labelledby="how">
        <h2 id="how" class="text-h3 font-semibold">{{ __('ui.home.how_title') }}</h2>
        <ol class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach (__('ui.home.steps') as $i => [$title, $body])
                <li class="rounded-panel border border-line bg-surface p-5">
                    <span class="section-num text-h3 leading-none">{{ $i + 1 }}</span>
                    <h3 class="mt-3 font-semibold">{{ $title }}</h3>
                    <p class="mt-1 text-small text-muted">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- 3. Other tools --}}
    <section class="mt-18" aria-labelledby="tools">
        <h2 id="tools" class="text-h3 font-semibold">{{ __('ui.home.tools_title') }}</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('ui.home.tools') as $route => $desc)
                <li>
                    <a href="{{ lroute($route) }}" class="block h-full rounded-panel border border-line bg-surface p-5 no-underline transition-colors hover:border-lav-200 hover:bg-lav-50">
                        <span class="block font-semibold text-ink">{{ __('ui.pages.'.str_replace('.', '-', $route)) }}</span>
                        <span class="mt-1 block text-small text-muted">{{ $desc }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- 4. FAQ --}}
    <section class="mt-18 max-w-3xl" aria-labelledby="faq">
        <h2 id="faq" class="text-h3 font-semibold">{{ __('ui.home.faq_title') }}</h2>
        <div class="mt-5 divide-y divide-line border-y border-line">
            @foreach (__('ui.home.faq') as [$q, $a])
                <details class="group py-4">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium min-h-11">
                        {{ $q }}
                        <span class="text-lav-700 text-lead leading-none group-open:rotate-45 transition-transform" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-2 text-muted">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </section>
</x-layouts.app>
