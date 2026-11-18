<x-layouts.app :title="$page->title" :description="$page->description">
    <x-page-intro :h1="$page->h1" :intro="$page->intro" />

    <x-tool.fixer />

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

    <div class="mt-18">
        <x-content-body :page="$page" />
        <x-related-tools :except="$software ? null : 'fix'" :software="$software" />
    </div>
</x-layouts.app>
