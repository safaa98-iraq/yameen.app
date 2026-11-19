@props(['font'])
@php $client = $font->toClient(); @endphp
{{-- The whole card links to the font page. No buttons: copying happens in the app picker. --}}
<article class="rounded-panel border border-line bg-surface overflow-hidden min-w-0 transition-colors hover:border-lav-500"
         x-data="fontCard(@js($client))" x-show="matches(font)">
    <a href="{{ lroute('fonts.show', ['font' => $font->slug]) }}" class="flex flex-col h-full text-ink no-underline">
        {{-- Specimen: the user's text in this font. Fixed height keeps the grid stable. --}}
        <div class="bg-lav-50 px-5 py-6 flex items-center overflow-hidden"
             :style="{ height: `${Math.round(size * 1.6 * 2 + 48)}px` }" style="height: 176px">
            <p class="w-full leading-snug break-words" dir="auto"
               :style="specimenStyle" style="font-size: 40px"
               x-text="text">{{ $font->sample_text ?: config('yameen.fonts.default_sample.'.app()->getLocale()) }}</p>
        </div>
        <div class="p-4 flex items-baseline justify-between gap-3">
            <h3 class="text-small font-semibold truncate" dir="ltr">{{ $font->name }}</h3>
            <p class="text-meta text-muted shrink-0">
                {{ __('ui.styles.'.$font->style) }}@if ($font->presentation_forms) · <span class="text-lav-700">{{ __('ui.fonts.apps_badge') }}</span>@endif
            </p>
        </div>
    </a>
</article>
