@props(['font'])
@php $client = $font->toClient(); @endphp
<article class="rounded-panel border border-line bg-surface overflow-hidden flex flex-col min-w-0 transition-colors hover:border-lav-200"
         x-data="fontCard(@js($client))" x-show="matches(font)"
         aria-labelledby="font-{{ $font->slug }}">
    {{-- Specimen: the user's text in this font. Fixed height keeps the grid stable. --}}
    <div class="bg-lav-50 px-5 py-6 flex items-center overflow-hidden"
         :style="{ height: `${Math.round(size * 1.6 * 2 + 48)}px` }" style="height: 176px">
        <p class="w-full leading-snug break-words text-ink" dir="auto"
           :style="specimenStyle" style="font-size: 40px"
           x-text="text">{{ $font->sample_text ?: config('yameen.fonts.default_sample.'.app()->getLocale()) }}</p>
    </div>

    <div class="p-4 flex items-center gap-3">
        <div class="min-w-0 flex-1">
            <h3 id="font-{{ $font->slug }}" class="text-small font-semibold truncate">
                <a href="{{ lroute('fonts.show', ['font' => $font->slug]) }}" class="text-ink no-underline hover:underline" dir="ltr">{{ $font->name }}</a>
            </h3>
            <p class="text-meta text-muted truncate">
                {{ __('ui.styles.'.$font->style) }} · {{ trans_choice('ui.fonts.weights_count', count($font->weights), ['count' => count($font->weights)]) }}
            </p>
        </div>
        @if ($font->presentation_forms)
            <button type="button" class="btn btn-primary shrink-0 min-w-24" @click="copyForApp()">
                <span x-show="!appCopied">{{ __('ui.fonts.copy_for_app') }}</span>
                <span x-show="appCopied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
        @else
            <a href="{{ lroute('fonts.show', ['font' => $font->slug]) }}" class="btn btn-secondary shrink-0">{{ __('ui.fonts.view') }}</a>
        @endif
    </div>
</article>
