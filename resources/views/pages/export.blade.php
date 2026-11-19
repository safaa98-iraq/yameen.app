<x-layouts.app :title="__('ui.export.title')" :description="__('ui.export.description')" :app="true"
               :breadcrumbs="[[__('ui.pages.export'), lroute('export')]]">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.export.h1')" :intro="__('ui.export.intro')" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8" x-data="exporter({ fonts: @js($fonts), font: @js($default) })"
         data-empty-message="{{ __('ui.export.empty') }}" data-fail-message="{{ __('ui.export.failed') }}">
        <div class="rounded-panel border border-line bg-surface overflow-hidden">
            <div class="p-4 md:p-6 space-y-5">
                <x-tool.input id="export-input" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="export-font" class="label">{{ __('ui.tool.font_label') }}</label>
                        <select id="export-font" x-model="font" class="field mt-2 h-11 px-3 text-small" dir="ltr">
                            @foreach ($fonts as $f)
                                <option value="{{ $f['slug'] }}" @selected($f['slug'] === $default)>{{ $f['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="export-weight" class="label">{{ __('ui.fonts.weight') }}</label>
                        <select id="export-weight" x-model.number="weight" class="field mt-2 h-11 px-3 text-small">
                            <template x-for="w in Object.keys(selected?.ttf ?? {})" :key="w">
                                <option :value="w" x-text="w" :selected="Number(w) === usedWeight"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="export-color" class="label">{{ __('ui.export.color') }}</label>
                        <input id="export-color" type="color" x-model="color" class="field mt-2 h-11 px-1 cursor-pointer">
                    </div>
                    <div>
                        <label for="export-width" class="label">{{ __('ui.export.width') }} <span class="tabular-nums" x-text="'(≤ ' + maxWidth + ' px)'"></span></label>
                        <input id="export-width" type="number" min="100" :max="maxWidth" step="50" x-model.number="width" class="field mt-2 h-11 px-3 text-small tabular-nums" dir="ltr">
                    </div>
                </div>

                <fieldset>
                    <legend class="label">{{ __('ui.export.align') }}</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (['right', 'center', 'left'] as $a)
                            <label class="chip"><input type="radio" class="sr-only" name="export-align" value="{{ $a }}" x-model="align" @checked($a === 'right')> {{ __('ui.export.align_'.$a) }}</label>
                        @endforeach
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="transparent" :disabled="!$store.credits.pro"> {{ __('ui.export.transparent') }} <span class="text-meta text-lav-700">Pro</span></label>
                    </div>
                </fieldset>
            </div>

            <section class="border-t border-line bg-lav-50 p-4 md:p-6" aria-labelledby="export-preview-label">
                <h2 id="export-preview-label" class="text-small font-semibold">{{ __('ui.export.preview') }}</h2>
                <div class="mt-4 rounded-control border border-line px-6 py-6 overflow-hidden"
                     :class="transparent && $store.credits.pro ? 'bg-[repeating-conic-gradient(var(--line)_0_25%,var(--surface)_0_50%)] bg-[length:16px_16px]' : 'bg-surface'">
                    <p class="text-h2 leading-normal whitespace-pre-wrap break-words" dir="auto" :style="previewStyle"
                       x-text="$store.text.value || @js(config('yameen.fonts.default_sample.'.app()->getLocale()))"></p>
                </div>
                <p x-show="error" x-cloak x-text="error" class="mt-3 text-small text-warn" role="alert"></p>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action @click="png()" :disabled="busy">
                        {{ __('ui.export.png') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="svg()" :disabled="busy">
                        {{ __('ui.export.svg') }} <span class="text-meta text-lav-700">Pro</span>
                    </button>
                    <span class="text-meta text-muted" x-show="busy" x-cloak>{{ __('ui.export.working') }}</span>
                </div>
                <p class="mt-3 text-meta text-muted">{{ __('ui.export.note') }}</p>
                <x-limit-panel class="mt-4" />
            </section>
        </div>
    </div>

    <x-related-tools except="export" />
</x-layouts.app>
