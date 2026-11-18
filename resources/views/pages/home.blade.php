<x-layouts.app>
    <x-page-intro :h1="__('ui.home.h1')" />

    <x-tool.fixer id="home" />

    <div class="mt-18">
        <x-section num="01" :title="__('ui.home.s1_title')">
            <p>{{ __('ui.home.s1_body') }}</p>
        </x-section>

        <x-section num="02" :title="__('ui.home.s2_title')">
            <ol class="space-y-3">
                @foreach (__('ui.home.s2_steps') as $i => $step)
                    <li class="grid grid-cols-[2rem_1fr] gap-2">
                        <span class="section-num tabular-nums">{{ sprintf('%02d', $i + 1) }}</span>
                        <span>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </x-section>

        <x-related-tools />
    </div>
</x-layouts.app>
