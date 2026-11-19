{{-- Numbered Markdown sections followed by the FAQ, from a content Page. --}}
@props(['page', 'start' => 1])
@foreach ($page->sections as $i => $section)
    <x-section :num="sprintf('%02d', $start + $i)" :title="$section['title']">
        <div class="content-md">{!! $section['html'] !!}</div>
    </x-section>
@endforeach
@if ($page->faq)
    <x-section :num="sprintf('%02d', $start + count($page->sections))" :title="__('ui.content.faq')" id="faq">
        <dl class="space-y-6">
            @foreach ($page->faq as $item)
                <div>
                    <dt class="font-semibold">{{ $item['q'] }}</dt>
                    <dd class="mt-1">{{ $item['a'] }}</dd>
                </div>
            @endforeach
        </dl>
    </x-section>
@endif
