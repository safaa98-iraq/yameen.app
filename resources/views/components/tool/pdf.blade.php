@props(['id' => 'pdf'])
<div class="grid gap-4 lg:grid-cols-2" x-data="pdfFixer">
    <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 flex flex-col gap-4" aria-label="{{ __('ui.pdf.input_label') }}">
        <x-tool.input :id="$id.'-input'" :label="__('ui.pdf.input_label')" :placeholder="__('ui.pdf.placeholder')" />
    </section>

    <section class="rounded-panel bg-surface border border-line p-4 md:p-6 flex flex-col gap-6" aria-labelledby="{{ $id }}-result-label">
        <h2 id="{{ $id }}-result-label" class="label">{{ __('ui.tool.result_label') }}</h2>

        <div class="flex-1 min-h-36" aria-live="polite">
            <p x-show="result.text" x-cloak x-text="result.text" dir="auto" data-result
               class="text-lead leading-relaxed whitespace-pre-wrap break-words"></p>
            <p x-show="!result.text" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>

        <p class="text-small" x-show="status !== 'empty'" x-cloak data-pdf-status
           :class="status === 'unsure' ? 'text-warn' : 'text-muted'">
            <span x-show="status === 'auto-reversed'">{{ __('ui.pdf.status_auto') }}</span>
            <span x-show="status === 'unsure'">{{ __('ui.pdf.status_unsure') }}</span>
            <span x-show="status === 'logical'">{{ __('ui.pdf.status_logical') }}</span>
            <span x-show="status === 'manual-reversed'">{{ __('ui.pdf.status_manual') }}</span>
            <span x-show="status === 'manual-kept'">{{ __('ui.pdf.status_kept') }}</span>
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action
                    @click="copy(result.text)" :disabled="!result.text">
                <span x-show="!copied">{{ __('ui.pdf.copy') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
            <button type="button" class="btn btn-secondary" @click="toggleReverse()" :disabled="!result.text"
                    x-text="result.reversedApplied ? @js(__('ui.pdf.undo_reverse')) : @js(__('ui.pdf.reverse'))">{{ __('ui.pdf.reverse') }}</button>
        </div>
    </section>
</div>
