@props(['font'])
@php $client = $font->toClient(); @endphp
<article class="border border-line rounded-panel bg-surface p-4 md:p-5 flex flex-col gap-4 min-w-0"
         x-data="fontCard(@js($client))" x-show="matches(font)"
         aria-labelledby="font-{{ $font->slug }}">
    <header class="flex items-baseline justify-between gap-3">
        <h3 id="font-{{ $font->slug }}" class="text-small font-medium">
            <a href="{{ lroute('fonts.show', ['font' => $font->slug]) }}" class="text-ink no-underline hover:underline" dir="ltr">{{ $font->name }}</a>
        </h3>
        @if ($font->presentation_forms)
            <span class="text-meta text-lav-700 shrink-0">{{ __('ui.fonts.apps_badge') }}</span>
        @endif
    </header>

    {{-- Fixed height per size keeps the grid from shifting while fonts load. --}}
    <p class="leading-snug break-words overflow-hidden text-ink" dir="auto"
       :style="{ ...specimenStyle, minHeight: `${size * 1.5 * 2}px`, maxHeight: `${size * 1.5 * 3}px` }"
       style="font-size: 40px; min-height: 120px; max-height: 180px"
       x-text="text">{{ $font->sample_text ?: config('yameen.fonts.default_sample.'.app()->getLocale()) }}</p>

    <p class="text-meta text-muted">
        {{ __('ui.styles.'.$font->style) }} · {{ trans_choice('ui.fonts.weights_count', count($font->weights), ['count' => count($font->weights)]) }} · {{ $font->license }}
    </p>

    <div class="flex flex-wrap gap-2 mt-auto">
        <button type="button" class="btn btn-secondary" @click="copyCss(@js($font->cssSnippet(400)))">
            <span x-show="!copied">{{ __('ui.fonts.copy_css') }}</span>
            <span x-show="copied" x-cloak>{{ __('ui.fonts.copied_css') }}</span>
        </button>
        @if ($font->presentation_forms)
            <a class="btn btn-secondary" href="{{ lroute('fix', ['font' => $font->slug]) }}">{{ __('ui.fonts.use_font') }}</a>
        @endif
    </div>
</article>
