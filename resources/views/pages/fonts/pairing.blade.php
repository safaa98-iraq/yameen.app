<x-layouts.app :title="__('ui.fonts.pairing_title')" :description="__('ui.fonts.pairing_description')">
    <x-page-intro :h1="__('ui.fonts.pairing_h1')" :intro="__('ui.fonts.pairing_intro')" />

    @forelse ($pairs as $i => $pair)
        @php
            $ar = $pair['arabic']; $la = $pair['latin'];
            $arStyle = "font-family: '{$ar->cssFamily()}', var(--font-ar)";
            $laStyle = $la ? "font-family: '{$la->cssFamily()}', var(--font-en)" : 'font-family: var(--font-en)';
        @endphp
        <section class="border-t border-line py-10 grid gap-6 md:grid-cols-[6rem_1fr]">
            <div class="section-num text-h3 leading-none" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</div>
            <div class="min-w-0">
                <h2 class="text-small text-muted">
                    <a href="{{ lroute('fonts.show', ['font' => $ar->slug]) }}" dir="ltr">{{ $ar->name }}</a>
                    + <span dir="ltr">{{ $pair['latin_name'] }}</span>
                    · {{ __('ui.fonts.pairing_use.'.$pair['use']) }}
                </h2>
                <div class="mt-4 grid gap-8 lg:grid-cols-2">
                    <div dir="rtl" lang="ar" x-data x-font="@js($ar->toClient())" data-weight="700">
                        <p class="text-h2 leading-snug" style="{{ $arStyle }}; font-weight: {{ $ar->nearestWeight(700) }}">{{ __('ui.fonts.pairing_heading_ar') }}</p>
                        <p class="mt-3 text-body leading-relaxed" style="{{ $arStyle }}" x-data x-font="@js($ar->toClient())">{{ __('ui.fonts.pairing_body_ar') }}</p>
                    </div>
                    <div dir="ltr" lang="en" @if ($la) x-data x-font="@js($la->toClient())" data-weight="700" @endif>
                        <p class="text-h2 leading-snug" style="{{ $laStyle }}; font-weight: {{ $la?->nearestWeight(700) ?? 700 }}">{{ __('ui.fonts.pairing_heading_en') }}</p>
                        <p class="mt-3 text-body leading-relaxed" style="{{ $laStyle }}" @if ($la) x-data x-font="@js($la->toClient())" @endif>{{ __('ui.fonts.pairing_body_en') }}</p>
                    </div>
                </div>
            </div>
        </section>
    @empty
        <p class="text-muted">{{ __('ui.fonts.empty_catalog') }}</p>
    @endforelse

    <x-related-tools except="fonts" />
</x-layouts.app>
