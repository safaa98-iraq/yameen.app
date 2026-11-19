@props(['id' => 'garbled'])
<div class="rounded-panel border border-line bg-surface overflow-hidden" x-data="garbledFixer">
    <div class="p-4 md:p-6">
        <x-tool.input :id="$id.'-input'" :label="__('ui.garbled.input_label')" :placeholder="__('ui.garbled.placeholder')" />
    </div>

    <section class="border-t border-line bg-lav-50 p-4 md:p-6" aria-labelledby="{{ $id }}-result-label">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="{{ $id }}-result-label" class="text-small font-semibold">{{ __('ui.tool.result_label') }}</h2>
            <p class="text-meta text-muted">{{ __('ui.garbled.legend') }} <mark class="bg-lav-100 text-ink rounded px-1">{{ __('ui.garbled.legend_sample') }}</mark></p>
        </div>

        <div class="mt-4 min-h-24 rounded-control bg-surface border border-line px-4 py-4" aria-live="polite">
            <p x-show="dictState === 'loading'" class="text-small text-muted">{{ __('ui.garbled.loading') }}</p>
            <p x-show="dictState === 'failed'" x-cloak class="text-small text-warn">{{ __('ui.pdf.words_failed') }}</p>
            <p x-show="dictState === 'ready' && !result.text" x-cloak class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
            <p x-show="result.text" x-cloak dir="auto" data-result class="text-lead leading-relaxed whitespace-pre-wrap break-words"><template x-for="(part, i) in result.parts" :key="i"><span :class="part.from && 'bg-lav-100 rounded'" :title="part.from ? @js(__('ui.garbled.was')) + ' ' + part.from : null" x-text="part.text"></span></template></p>
        </div>

        <p class="mt-3 text-small text-lav-700" x-show="result.text" x-cloak role="status" data-garbled-status
           x-text="result.fixed ? @js(__('ui.garbled.fixed', ['count' => '__N__'])).replace('__N__', result.fixed) : @js(__('ui.garbled.none'))"></p>

        <button type="button" class="mt-4 btn btn-primary btn-primary-lg min-w-48" data-primary-action
                @click="copy(result.text)" :disabled="!result.text">
            <span x-show="!copied">{{ __('ui.pdf.copy') }}</span>
            <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
        </button>
    </section>
</div>
