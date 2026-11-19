@php
    $sample = $font->sample_text ?: config('yameen.fonts.default_sample.'.app()->getLocale());
    $letters = ['ا','ب','ت','ث','ج','ح','خ','د','ذ','ر','ز','س','ش','ص','ض','ط','ظ','ع','غ','ف','ق','ك','ل','م','ن','ه','و','ي','ء','آ','أ','إ','ؤ','ئ','ة','ى','لا'];
    $digits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
@endphp
<x-layouts.app :title="__('ui.fonts.font_title', ['font' => $font->name])" :description="__('ui.fonts.font_description', ['font' => $font->name])"
               :image="$font->ogUrl()"
               :breadcrumbs="[[__('ui.pages.fonts'), lroute('fonts')], [$font->name, lroute('fonts.show', ['font' => $font->slug])]]">
    <div x-data="fontSpecimen(@js($font->toClient()), @js($sample))">
        <div class="pt-8 md:pt-12 pb-6 flex flex-wrap items-baseline justify-between gap-4">
            <h1 class="text-h3 md:text-h2 font-semibold leading-tight" dir="ltr">{{ $font->name }}</h1>
            <p class="text-small text-muted">
                {{ __('ui.styles.'.$font->style) }} · {{ trans_choice('ui.fonts.weights_count', count($font->weights), ['count' => count($font->weights)]) }} · {{ $font->license }}
                @if ($font->presentation_forms) · <span class="text-lav-700">{{ __('ui.fonts.apps_badge') }}</span> @endif
            </p>
        </div>

        <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6" aria-label="{{ __('ui.fonts.specimen_label') }}">
            <x-tool.input id="font-text" :label="__('ui.fonts.specimen_label')" />
            <p class="mt-6 leading-normal break-words text-specimen-lg min-h-[1.5em]" dir="auto"
               :style="styleFor(400)" x-text="text">{{ $sample }}</p>
        </section>

        <x-section num="01" :title="__('ui.fonts.all_weights')">
            <ul class="divide-y divide-line">
                @foreach ($font->weights as $w)
                    <li class="py-3 grid grid-cols-[3rem_1fr] gap-4 items-baseline">
                        <span class="text-meta text-muted tabular-nums">{{ $w }}</span>
                        <span class="text-h3 leading-normal truncate" dir="auto" :style="styleFor({{ $w }})" x-text="text">{{ $sample }}</span>
                    </li>
                @endforeach
            </ul>
        </x-section>

        <x-section num="02" :title="__('ui.fonts.glyphs')">
            <div class="grid grid-cols-6 sm:grid-cols-8 border-t border-s border-line" dir="rtl" :style="styleFor(400)">
                @foreach (array_merge($letters, $digits) as $g)
                    <span class="aspect-square border-b border-e border-line flex items-center justify-center text-h3">{{ $g }}</span>
                @endforeach
            </div>
        </x-section>

        <x-section num="03" :title="__('ui.fonts.license')">
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2">
                <dt class="text-muted">{{ __('ui.fonts.designer') }}</dt><dd dir="ltr" class="text-start">{{ $font->designer ?: '—' }}</dd>
                <dt class="text-muted">{{ __('ui.fonts.license') }}</dt>
                <dd>SIL Open Font License
                    @if ($font->licenseUrl()) · <a href="{{ $font->licenseUrl() }}" class="underline">{{ __('ui.fonts.license_file') }}</a> @endif
                </dd>
            </dl>
            <div class="mt-6 flex flex-wrap gap-2">
                @if ($font->google_url)
                    <a href="{{ $font->google_url }}" class="btn btn-secondary" rel="noopener" target="_blank">{{ __('ui.fonts.source') }}</a>
                @endif
                @if (auth()->user()?->isPro())
                    <form method="POST" action="{{ lroute('favorites.store') }}">
                        @csrf
                        <input type="hidden" name="type" value="font"><input type="hidden" name="value" value="{{ $font->slug }}">
                        <button class="btn btn-secondary">{{ __('ui.fonts.favorite') }}</button>
                    </form>
                @endif
            </div>
        </x-section>

        <x-section num="04" :title="__('ui.fonts.css')">
            <pre class="bg-surface border border-line rounded-panel p-4 text-meta overflow-x-auto" dir="ltr"><code>{{ $font->cssSnippet() }}</code></pre>
        </x-section>
    </div>

    <section class="border-t border-line py-12" aria-labelledby="font-fixer-title">
        <div class="grid gap-4 md:grid-cols-[6rem_1fr] mb-6">
            <div class="section-num text-h3 leading-none pt-1" aria-hidden="true">05</div>
            <div>
                <h2 id="font-fixer-title" class="text-h3 font-semibold leading-snug">{{ __('ui.fonts.fixer_with', ['font' => $font->name]) }}</h2>
                @unless ($font->presentation_forms)
                    <p class="mt-4 max-w-prose text-muted">{{ __('ui.fonts.no_apps_note') }}</p>
                @endunless
            </div>
        </div>
        @if ($font->presentation_forms)
            <x-tool.fixer id="font-fixer" :font="$font->slug" />
        @endif
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-12 border-t border-line pt-12">
            <h2 class="label">{{ __('ui.fonts.related') }}</h2>
            <div class="mt-3 grid gap-4 md:grid-cols-3" x-data="fontGrid({ sample: @js($sample) })">
                @foreach ($related as $r)
                    <x-font-card :font="$r" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
