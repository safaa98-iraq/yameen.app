{{--
    Shared tool layout: input panel on --lav-50, result panel on --surface.
    Phase 0 only wires shared text, auto-grow and copy feedback; the fixer
    pipeline replaces `result` in phase 1.
--}}
@props(['id' => 'tool'])
<div class="grid gap-4 lg:grid-cols-2" x-data="{
        copied: false,
        font: 'IBM Plex Sans Arabic',
        get result() { return $store.text.value },
        async copy() {
            if (!this.result) return;
            try { await navigator.clipboard.writeText(this.result) } catch { return }
            this.copied = true;
            setTimeout(() => this.copied = false, 1500);
        },
    }">
    <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 flex flex-col gap-4" aria-labelledby="{{ $id }}-input-label">
        <div>
            <label for="{{ $id }}-input" id="{{ $id }}-input-label" class="label">{{ __('ui.tool.input_label') }}</label>
            <textarea id="{{ $id }}-input" dir="auto" rows="4" x-autogrow
                      class="field mt-2 p-4 text-lead leading-relaxed resize-none min-h-36"
                      placeholder="{{ __('ui.tool.placeholder') }}"
                      :value="$store.text.value" @input="$store.text.set($event.target.value)"></textarea>
        </div>

        <fieldset>
            <legend class="label">{{ __('ui.tool.options_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                <label class="chip"><input type="checkbox" class="sr-only" checked> {{ __('ui.tool.opt_strip_tashkeel') }}</label>
                <label class="chip"><input type="checkbox" class="sr-only" checked> {{ __('ui.tool.opt_shape') }}</label>
                <label class="chip"><input type="checkbox" class="sr-only" checked> {{ __('ui.tool.opt_reverse') }}</label>
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
            <h2 id="{{ $id }}-result-label" class="label">{{ __('ui.tool.result_label') }}</h2>
            <span class="text-meta text-muted hidden md:inline">{{ __('ui.tool.shortcut_hint') }}</span>
        </div>

        <div class="flex-1 min-h-36" aria-live="polite">
            <p x-show="result" x-text="result" :style="{ fontFamily: `'${font}', var(--font-ar)` }"
               class="text-h2 leading-normal whitespace-pre-wrap break-words" dir="auto"></p>
            <p x-show="!result" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action @click="copy()">
                <span x-show="!copied">{{ __('ui.tool.copy_for_app') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
            <a href="{{ lroute('export') }}" class="btn btn-secondary">{{ __('ui.tool.export_image') }}</a>
        </div>
    </section>
</div>
