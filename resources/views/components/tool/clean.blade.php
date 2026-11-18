@props(['id' => 'clean'])
@php
    $checks = ['tashkeel' => 'tashkeel', 'tatweel' => 'tatweel', 'alef' => 'alef', 'yaMaqsura' => 'ya', 'spaces' => 'spaces', 'trimLines' => 'trim'];
@endphp
<div class="grid gap-4 lg:grid-cols-2" x-data="cleaner">
    <section class="rounded-panel bg-lav-50 border border-line p-4 md:p-6 flex flex-col gap-4" aria-label="{{ __('ui.tool.input_label') }}">
        <x-tool.input :id="$id.'-input'" />

        <fieldset>
            <legend class="label">{{ __('ui.cleaner.options_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($checks as $key => $label)
                    <label class="chip"><input type="checkbox" class="sr-only" x-model="opts.{{ $key }}" @checked(in_array($key, ['tashkeel', 'tatweel', 'spaces', 'trimLines']))> {{ __('ui.cleaner.'.$label) }}</label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="label">{{ __('ui.cleaner.digits_label') }}</legend>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach (['none', 'western', 'arabic'] as $mode)
                    <label class="chip"><input type="radio" name="{{ $id }}-digits" value="{{ $mode }}" class="sr-only" x-model="opts.digits" @checked($mode === 'none')> {{ __('ui.cleaner.digits_'.$mode) }}</label>
                @endforeach
            </div>
        </fieldset>
    </section>

    <section class="rounded-panel bg-surface border border-line p-4 md:p-6 flex flex-col gap-6" aria-labelledby="{{ $id }}-result-label">
        <h2 id="{{ $id }}-result-label" class="label">{{ __('ui.tool.result_label') }}</h2>
        <div class="flex-1 min-h-36" aria-live="polite">
            <p x-show="output" x-cloak x-text="output" dir="auto" data-result
               class="text-lead leading-relaxed whitespace-pre-wrap break-words"></p>
            <p x-show="!output" class="text-small text-muted">{{ __('ui.tool.empty') }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action
                    @click="copy(output)" :disabled="!output">
                <span x-show="!copied">{{ __('ui.cleaner.copy') }}</span>
                <span x-show="copied" x-cloak>{{ __('ui.tool.copied') }}</span>
            </button>
        </div>
    </section>
</div>
