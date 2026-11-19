@php $pro = auth()->user()?->isPro(); @endphp
<x-layouts.app :title="__('ui.batch.title')" :description="__('ui.batch.description')" :app="true"
               :breadcrumbs="[[__('ui.pages.batch'), lroute('batch')]]">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.batch.h1')" :intro="__('ui.batch.intro')" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8">
        @if (! $pro)
            <div class="rounded-panel border border-line bg-surface p-5 md:p-6">
                <p class="font-semibold">{{ __('ui.batch.pro_title') }}</p>
                <p class="mt-1 text-small text-muted">{{ __('ui.batch.pro_body') }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ lroute('pricing') }}" class="btn btn-primary">{{ __('ui.credits_ui.subscribe') }}</a>
                    @guest <a href="{{ lroute('login') }}" class="btn btn-secondary">{{ __('ui.nav.login') }}</a> @endguest
                </div>
            </div>
        @else
            <div class="rounded-panel border border-line bg-surface p-5 md:p-6 space-y-5" x-data="batchTool"
                 data-pro-message="{{ __('ui.credits_ui.pro_only') }}" data-type-message="{{ __('ui.batch.bad_type') }}"
                 data-size-message="{{ __('ui.batch.too_big') }}" data-parse-message="{{ __('ui.batch.bad_file') }}">
                <div>
                    <label for="batch-file" class="block font-semibold">{{ __('ui.batch.file') }}</label>
                    <input id="batch-file" type="file" accept=".srt,.txt,.csv,.json" @change="choose($event)"
                           class="mt-2 block w-full text-small file:btn file:btn-secondary file:me-3">
                    <p class="mt-1 text-meta text-muted">{{ __('ui.batch.formats') }}</p>
                </div>

                <fieldset x-show="fields.length" x-cloak>
                    <legend class="font-semibold" x-text="type === 'csv' ? @js(__('ui.batch.columns')) : @js(__('ui.batch.keys'))"></legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="f in fields" :key="f">
                            <label class="chip"><input type="checkbox" class="sr-only" :value="f" x-model="chosen"> <span x-text="f" dir="auto"></span></label>
                        </template>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="font-semibold">{{ __('ui.tool.options_label') }}</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="options.stripTashkeel"> {{ __('ui.tool.opt_strip_tashkeel') }}</label>
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="options.shape"> {{ __('ui.tool.opt_shape') }}</label>
                        <label class="chip"><input type="checkbox" class="sr-only" x-model="options.reverse"> {{ __('ui.tool.opt_reverse') }}</label>
                    </div>
                </fieldset>

                <p x-show="error" x-cloak x-text="error" class="text-small text-warn" role="alert"></p>
                <p x-show="done" x-cloak class="text-small text-lav-700" role="status">{{ __('ui.batch.done') }}</p>
                <button type="button" class="btn btn-primary btn-primary-lg min-w-48" data-primary-action :disabled="!file" @click="run()">{{ __('ui.batch.run') }}</button>
            </div>
        @endif
    </div>

    <x-related-tools />
</x-layouts.app>
