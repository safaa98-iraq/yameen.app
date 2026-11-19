<x-layouts.app :title="__('ui.favorites.title')" :description="__('ui.favorites.title')" :noindex="true">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.favorites.h1')" :intro="__('ui.favorites.intro')" />
    </x-slot:hero>

    <div class="max-w-4xl mx-auto pt-8 space-y-8">
        @if (! $pro)
            <div class="rounded-panel border border-line bg-surface p-5">
                <p class="font-semibold">{{ __('ui.favorites.pro_only') }}</p>
                <a href="{{ lroute('pricing') }}" class="btn btn-primary mt-4">{{ __('ui.credits_ui.subscribe') }}</a>
            </div>
        @else
            @if (session('status'))
                <p class="rounded-panel bg-lav-100 border border-lav-200 p-3 text-small" role="status">{{ session('status') }}</p>
            @endif
            <section x-data>
                <h2 class="font-semibold">{{ __('ui.favorites.save_text') }}</h2>
                <form method="POST" action="{{ lroute('favorites.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="type" value="text">
                    <label for="fav-text" class="sr-only">{{ __('ui.tool.input_label') }}</label>
                    <textarea id="fav-text" name="value" rows="2" maxlength="2000" required dir="auto" class="field p-3"
                              x-init="$el.value = $store.text.value"></textarea>
                    <button class="btn btn-secondary">{{ __('ui.favorites.save') }}</button>
                </form>
            </section>

            <section>
                <h2 class="font-semibold">{{ __('ui.favorites.texts') }}</h2>
                @forelse ($texts as $fav)
                    <div class="mt-3 flex items-start justify-between gap-3 rounded-panel border border-line bg-surface p-4" x-data>
                        <p class="flex-1 whitespace-pre-wrap break-words" dir="auto">{{ $fav->value }}</p>
                        <div class="flex shrink-0 gap-2">
                            <a href="{{ lroute('fix') }}" class="btn btn-secondary" @click="$store.text.set(@js($fav->value))">{{ __('ui.favorites.use') }}</a>
                            <form method="POST" action="{{ lroute('favorites.destroy', ['favorite' => $fav->id]) }}">@csrf @method('DELETE')<button class="btn btn-secondary">{{ __('ui.favorites.remove') }}</button></form>
                        </div>
                    </div>
                @empty
                    <p class="mt-2 text-small text-muted">{{ __('ui.favorites.none') }}</p>
                @endforelse
            </section>

            <section>
                <h2 class="font-semibold">{{ __('ui.favorites.fonts') }}</h2>
                @if ($fonts->isEmpty())
                    <p class="mt-2 text-small text-muted">{{ __('ui.favorites.none_fonts') }}</p>
                @else
                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" x-data="fontGrid({ sample: @js(config('yameen.fonts.default_sample.'.app()->getLocale())) })">
                        @foreach ($fonts as $item)
                            <x-font-card :font="$item['font']" />
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-layouts.app>
