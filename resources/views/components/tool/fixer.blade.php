{{-- Fixer: one card, top to bottom: type → see the result → copy. --}}
@props(['id' => 'fixer', 'font' => null])
@inject('catalog', 'App\Support\FontCatalog')
@php
    $appFonts = $catalog->forApps();
    $selected = $catalog->find($font ?? request()->query('font'));
    $selected = ($selected && $selected->presentation_forms) ? $selected : $catalog->defaultForApps();
@endphp
<div class="rounded-panel border border-line bg-surface overflow-hidden"
     x-data="fixer({ fonts: @js($appFonts->map->toClient()->values()), font: @js($selected?->slug) })">
    <div class="p-4 md:p-6">
        <x-tool.input :id="$id.'-input'" :label="__('ui.home.input_label')" :placeholder="__('ui.home.placeholder')" />
        <p class="mt-2 text-small text-lav-700" x-show="restored" x-cloak role="status" data-restored>{{ __('ui.fixer.restored') }}</p>
    </div>

    <section class="border-t border-line bg-lav-50 p-4 md:p-6" aria-labelledby="{{ $id }}-result-label">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="{{ $id }}-result-label" class="text-small font-semibold">{{ __('ui.fixer.result_title') }}</h2>
            <div class="flex items-center gap-2">
                <label for="{{ $id }}-font" class="text-small text-muted">{{ __('ui.tool.font_label') }}</label>
                <select id="{{ $id }}-font" x-model="font" class="field h-10 px-3 text-small w-auto max-w-56" dir="ltr">
                    @forelse ($appFonts as $f)
                        <option value="{{ $f->slug }}" @selected($selected?->is($f))>{{ $f->name }}</option>
                    @empty
                        <option value="">IBM Plex Sans Arabic</option>
                    @endforelse
                </select>
            </div>
        </div>

        {{-- Shown exactly as an app without Arabic support will display it. --}}
        <div class="mt-4 min-h-24 rounded-control bg-surface border border-line px-4 py-4" aria-live="polite">
            <p x-show="output" x-cloak x-text="output" dir="ltr" data-result
               :style="{ fontFamily, unicodeBidi: 'bidi-override' }"
               class="text-h2 leading-normal whitespace-pre-wrap break-words text-end"></p>
            <p x-show="!output" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2">
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action
                    @click="copy(output, 'app')" :disabled="!output">
                <span x-show="!copied">{{ __('ui.tool.copy_for_app') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
            <p class="text-small text-muted flex-1 min-w-48">{{ __('ui.fixer.paste_hint') }}</p>
        </div>

        <details class="mt-5 group">
            <summary class="inline-flex items-center gap-2 cursor-pointer list-none text-small text-muted hover:text-ink min-h-11">
                <span class="text-lav-700 group-open:rotate-45 transition-transform" aria-hidden="true">+</span>
                {{ __('ui.fixer.advanced') }}
            </summary>
            <fieldset class="mt-2">
                <legend class="sr-only">{{ __('ui.tool.options_label') }}</legend>
                <div class="flex flex-wrap gap-2">
                    <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.stripTashkeel" checked> {{ __('ui.tool.opt_strip_tashkeel') }}</label>
                    <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.shape" checked> {{ __('ui.tool.opt_shape') }}</label>
                    <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.reverse" checked> {{ __('ui.tool.opt_reverse') }}</label>
                </div>
                <p class="mt-3 text-meta text-muted">{{ __('ui.fixer.font_note') }} {{ __('ui.fixer.reversed_note') }}</p>
            </fieldset>
        </details>
        <x-limit-panel class="mt-4" />
    </section>
</div>
