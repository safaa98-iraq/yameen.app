{{-- Big centred input, like a search bar: type, then fix and copy in one click. --}}
@props(['h1', 'lead' => null])
<section class="bg-lav-50 border-b border-line">
    <div class="max-w-3xl mx-auto px-4 pt-10 pb-10 md:pt-16 md:pb-14 text-center">
        <h1 class="text-h3 md:text-h1 font-semibold leading-tight">{{ $h1 }}</h1>
        @if ($lead)
            <p class="mt-3 text-body md:text-lead text-muted">{{ $lead }}</p>
        @endif

        <div x-data="quickFix" class="mt-8">
            <div class="flex flex-col sm:flex-row items-stretch gap-2 rounded-panel border-2 border-lav-200 bg-surface p-2 text-start focus-within:border-lav-700 transition-colors">
                <label for="hero-input" class="sr-only">{{ __('ui.home.input_label') }}</label>
                <textarea id="hero-input" x-ref="input" rows="1" dir="auto" x-autogrow spellcheck="false"
                          class="flex-1 min-h-14 px-4 py-3 text-lead leading-relaxed bg-transparent resize-none border-0 focus:outline-none focus-visible:outline-none"
                          placeholder="{{ __('ui.home.placeholder') }}"
                          :value="$store.text.value" @input="$store.text.set($event.target.value)"></textarea>
                <button type="button" class="btn btn-primary btn-primary-lg sm:min-w-40" data-primary-action @click="run()">
                    <span x-show="!copied">{{ __('ui.home.copy') }}</span>
                    <span x-show="copied" x-cloak>{{ __('ui.home.copied') }}</span>
                </button>
            </div>
        </div>

        <p class="mt-5 flex flex-wrap items-center justify-center gap-2 text-small text-muted">
            <span>{{ __('ui.home.try_in') }}</span>
            @foreach (config('yameen.software') as $slug => $name)
                <a href="{{ lroute('fix.software', ['software' => $slug]) }}" dir="ltr"
                   class="inline-flex items-center min-h-9 px-3 rounded-full border border-line bg-surface text-ink no-underline hover:border-lav-200">{{ $name }}</a>
            @endforeach
        </p>
    </div>
</section>
