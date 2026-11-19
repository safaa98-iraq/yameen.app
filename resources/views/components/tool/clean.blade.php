@props(['id' => 'clean'])
@php
    $checks = ['tashkeel' => 'tashkeel', 'tatweel' => 'tatweel', 'alef' => 'alef', 'yaMaqsura' => 'ya', 'spaces' => 'spaces', 'trimLines' => 'trim'];
@endphp
<div class="rounded-panel border border-line bg-surface overflow-hidden" x-data="cleaner">
    <div class="p-4 md:p-6 flex flex-col gap-5">
        <x-tool.input :id="$id.'-input'" />

        <fieldset>
            <legend class="text-small font-semibold">{{ __('ui.cleaner.options_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($checks as $key => $label)
                    <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.{{ $key }}" @checked(in_array($key, ['tashkeel', 'tatweel', 'spaces', 'trimLines']))> {{ __('ui.cleaner.'.$label) }}</label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-small font-semibold">{{ __('ui.cleaner.digits_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach (['none', 'western', 'arabic'] as $mode)
                    <label class="chip"><input type="radio" name="{{ $id }}-digits" value="{{ $mode }}" class="sr-only" x-model="opts.digits" @checked($mode === 'none')> {{ __('ui.cleaner.digits_'.$mode) }}</label>
                @endforeach
            </div>
        </fieldset>
    </div>

    <section class="border-t border-line bg-lav-50 p-4 md:p-6" aria-labelledby="{{ $id }}-result-label">
        <h2 id="{{ $id }}-result-label" class="text-small font-semibold">{{ __('ui.tool.result_label') }}</h2>
        <div class="mt-4 min-h-24 rounded-control bg-surface border border-line px-4 py-4" aria-live="polite">
            <p x-show="output" x-cloak x-text="output" dir="auto" data-result
               class="text-lead leading-relaxed whitespace-pre-wrap break-words"></p>
            <p x-show="!output" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>
        <button type="button" class="mt-4 btn btn-primary btn-primary-lg min-w-48" data-primary-action
                @click="copy(output)" :disabled="!output">
            <span x-show="!copied">{{ __('ui.cleaner.copy') }}</span>
            <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
        </button>
        <x-limit-panel class="mt-4" />
    </section>
</div>
