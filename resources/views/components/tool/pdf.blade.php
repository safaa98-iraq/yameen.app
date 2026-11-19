@props(['id' => 'pdf'])
<div class="rounded-panel border border-line bg-surface overflow-hidden" x-data="pdfFixer">
    <div class="p-4 md:p-6">
        <x-tool.input :id="$id.'-input'" :label="__('ui.pdf.input_label')" :placeholder="__('ui.pdf.placeholder')" />
    </div>

    <section class="border-t border-line bg-lav-50 p-4 md:p-6" aria-labelledby="{{ $id }}-result-label">
        <h2 id="{{ $id }}-result-label" class="text-small font-semibold">{{ __('ui.tool.result_label') }}</h2>
        <div class="mt-4 min-h-24 rounded-control bg-surface border border-line px-4 py-4" aria-live="polite">
            <p x-show="result.text" x-cloak x-text="result.text" dir="auto" data-result
               class="text-lead leading-relaxed whitespace-pre-wrap break-words"></p>
            <p x-show="!result.text" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>

        <p class="mt-3 text-small" x-show="status !== 'empty'" x-cloak data-pdf-status
           :class="status === 'unsure' ? 'text-warn' : 'text-muted'">
            <span x-show="status === 'auto-reversed'">{{ __('ui.pdf.status_auto') }}</span>
            <span x-show="status === 'unsure'">{{ __('ui.pdf.status_unsure') }}</span>
            <span x-show="status === 'logical'">{{ __('ui.pdf.status_logical') }}</span>
            <span x-show="status === 'manual-reversed'">{{ __('ui.pdf.status_manual') }}</span>
            <span x-show="status === 'manual-kept'">{{ __('ui.pdf.status_kept') }}</span>
        </p>

        <div class="mt-2 text-small" x-show="swapped" x-cloak role="status" data-words-status>
            <p x-show="dictState === 'loading'" class="text-muted">{{ __('ui.pdf.words_loading') }}</p>
            <p x-show="dictState === 'failed'" class="text-warn">{{ __('ui.pdf.words_failed') }}</p>
            <p x-show="dictState === 'ready' && repairWords" class="text-lav-700"
               x-text="@js(__('ui.pdf.words_fixed', ['count' => '__N__'])).replace('__N__', result.wordsFixed)"></p>
            <p x-show="dictState === 'ready' && !repairWords" class="text-muted">{{ __('ui.pdf.words_off') }}</p>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action
                    @click="copy(result.text)" :disabled="!result.text">
                <span x-show="!copied">{{ __('ui.pdf.copy') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
            <button type="button" class="btn btn-secondary" @click="toggleReverse()" :disabled="!result.text"
                    x-text="result.reversedApplied ? @js(__('ui.pdf.undo_reverse')) : @js(__('ui.pdf.reverse'))">{{ __('ui.pdf.reverse') }}</button>
            <button type="button" class="btn btn-secondary" x-show="swapped && dictState === 'ready'" x-cloak
                    @click="repairWords = !repairWords"
                    x-text="repairWords ? @js(__('ui.pdf.words_undo')) : @js(__('ui.pdf.words_redo'))"></button>
        </div>
        <x-limit-panel class="mt-4" />
    </section>
</div>
