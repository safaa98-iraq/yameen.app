{{-- Unnumbered Markdown sections (legal pages, blog posts). --}}
@props(['page'])
<div class="max-w-3xl mx-auto content-md">
    @foreach ($page->sections as $section)
        <h2 class="text-h3 font-semibold leading-snug pt-6">{{ $section['title'] }}</h2>
        {!! $section['html'] !!}
    @endforeach
</div>
