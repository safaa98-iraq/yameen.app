{{-- Fixer: input panel on --lav-50, result panel on --surface. --}}
@props(['id' => 'fixer', 'font' => 'IBM Plex Sans Arabic'])
<div class="grid gap-4 lg:grid-cols-2" x-data="fixer({ font: @js($font) })">
    <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 flex flex-col gap-4" aria-label="{{ __('ui.tool.input_label') }}">
        <x-tool.input :id="$id.'-input'" />

        <fieldset>
            <legend class="label">{{ __('ui.tool.options_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.stripTashkeel" checked> {{ __('ui.tool.opt_strip_tashkeel') }}</label>
                <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.shape" checked> {{ __('ui.tool.opt_shape') }}</label>
                <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.reverse" checked> {{ __('ui.tool.opt_reverse') }}</label>
            </div>
        </fieldset>

        <div>
            <label for="{{ $id }}-font" class="label">{{ __('ui.tool.font_label') }}</label>
            <select id="{{ $id }}-font" x-model="font" class="field mt-2 h-11 px-3 text-small">
                <option value="IBM Plex Sans Arabic">IBM Plex Sans Arabic</option>
            </select>
        </div>
    </section>

    <section class="rounded-panel bg-surface border border-line p-4 md:p-6 flex flex-col gap-6" aria-labelledby="{{ $id }}-result-label">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="{{ $id }}-result-label" class="label">{{ __('ui.tool.result_label') }} <span class="font-normal">· {{ __('ui.fixer.result_hint') }}</span></h2>
            <span class="text-meta text-muted hidden md:inline">{{ __('ui.tool.shortcut_hint') }}</span>
        </div>

        {{-- Shown exactly as an app without bidi support will display it: left to right, no reordering. --}}
        <div class="flex-1 min-h-36" aria-live="polite">
            <p x-show="output" x-cloak x-text="output" dir="ltr" data-result
               :style="{ fontFamily: `'${font}', var(--font-ar)`, unicodeBidi: 'bidi-override' }"
               class="text-h2 leading-normal whitespace-pre-wrap break-words text-end"></p>
            <p x-show="!output" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action
                    @click="copy(output)" :disabled="!output">
                <span x-show="!copied">{{ __('ui.tool.copy_for_app') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
            <a href="{{ lroute('export') }}" class="btn btn-secondary">{{ __('ui.tool.export_image') }}</a>
        </div>
        <p class="text-meta text-muted">{{ __('ui.fixer.font_note') }}</p>
    </section>
</div>
