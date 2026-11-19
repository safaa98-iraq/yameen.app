<x-layouts.app :title="$page->title" :description="$page->description">
    <x-slot:hero>
        <x-page-hero :h1="$page->h1" :intro="$page->intro" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8">

    @if ($software)
        <x-tool.fixer />
    @else
        <x-app-picker />
    @endif

    @if ($software && $page->meta('paste_steps'))
        <aside class="mt-4 rounded-panel border border-line bg-surface p-4 md:p-6" aria-labelledby="paste-title">
            <h2 id="paste-title" class="text-lead font-semibold">{{ __('ui.fixer.paste_title', ['app' => $softwareName]) }}</h2>
            <ol class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($page->meta('paste_steps') as $i => $step)
                    <li class="grid grid-cols-[2rem_1fr] gap-2">
                        <span class="section-num tabular-nums">{{ sprintf('%02d', $i + 1) }}</span>
                        <span>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
            @if ($page->meta('paste_note'))
                <p class="mt-4 text-small text-muted">{{ $page->meta('paste_note') }}</p>
            @endif
        </aside>
    @endif

    </div>

    <div class="mt-18">
        <x-content-body :page="$page" />
        <x-related-tools :except="$software ? null : 'fix'" :software="$software" />
    </div>
</x-layouts.app>
