{{--
    The main tool: 1) type, 2) click your app. Clicking an app copies the fixed
    text and shows how to paste it there. One kind of action only.
--}}
@inject('content', 'App\Support\Content\ContentRepository')
@php
    $apps = collect(config('yameen.software'))->map(fn ($name, $slug) => [
        'slug' => $slug,
        'name' => $name,
        'steps' => $content->find('fix-'.$slug)?->meta('paste_steps') ?? [],
    ])->values();
    $apps->push(['slug' => 'other', 'name' => __('ui.picker.other'), 'steps' => __('ui.picker.other_steps')]);
@endphp
<div x-data="appPicker(@js($apps))" class="rounded-panel border border-line bg-surface p-4 md:p-6 text-start">
    <label for="picker-input" class="block font-semibold">{{ __('ui.picker.step1') }}</label>
    <textarea id="picker-input" x-ref="input" rows="2" dir="auto" x-autogrow spellcheck="false"
              class="field mt-3 px-4 py-3 text-lead leading-relaxed resize-none min-h-20"
              placeholder="{{ __('ui.home.placeholder') }}"
              :value="$store.text.value" @input="$store.text.set($event.target.value)"></textarea>
    <p x-show="empty" x-cloak class="mt-2 text-small text-warn" role="alert">{{ __('ui.picker.empty') }}</p>

    <h2 class="mt-6 font-semibold">{{ __('ui.picker.step2') }}</h2>
    <p class="mt-1 text-small text-muted">{{ __('ui.picker.step2_hint') }}</p>
    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2">
        @foreach ($apps as $app)
            <button type="button" @click="pick(@js($app['slug']))"
                    class="btn btn-secondary btn-primary-lg w-full"
                    :class="picked === @js($app['slug']) && '!bg-lav-700 !text-surface !border-lav-700'"
                    @if ($app['slug'] !== 'other') dir="ltr" @endif
                    data-app="{{ $app['slug'] }}">{{ $app['name'] }}</button>
        @endforeach
    </div>

    <x-limit-panel class="mt-5" />

    {{-- After a click: confirmation + how to paste into the chosen app. --}}
    <div x-show="picked" x-cloak x-ref="done" role="status" class="mt-5 rounded-panel bg-lav-100 border border-lav-200 p-4 md:p-5">
        <p class="font-semibold text-lav-800">
            <span x-show="picked !== 'other'" x-text="@js(__('ui.picker.done', ['app' => '__APP__'])).replace('__APP__', app?.name ?? '')"></span>
            <span x-show="picked === 'other'">{{ __('ui.picker.done_other') }}</span>
        </p>
        <ol class="mt-3 space-y-2">
            <template x-for="(step, i) in app?.steps ?? []" :key="i">
                <li class="grid grid-cols-[1.75rem_1fr] gap-1">
                    <span class="section-num" x-text="i + 1"></span>
                    <span x-text="step"></span>
                </li>
            </template>
        </ol>
        <p class="mt-4 text-small text-muted">{{ __('ui.picker.preview') }}</p>
        <p class="mt-1 rounded-control bg-surface border border-line px-4 py-3 text-h3 leading-normal whitespace-pre-wrap break-words text-end"
           dir="ltr" style="unicode-bidi: bidi-override" x-text="copiedText" data-result></p>
        <p class="mt-3 text-meta text-muted">{{ __('ui.picker.warning') }}</p>
    </div>
</div>
