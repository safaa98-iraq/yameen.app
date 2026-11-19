@props(['except' => null, 'software' => null])
@php
    $tools = collect(['fix', 'pdf-fix', 'garbled', 'clean', 'fonts'])->reject(fn ($t) => $t === $except);
    $apps = collect(config('yameen.software'))->reject(fn ($name, $slug) => $slug === $software);
@endphp
<section class="border-t border-line py-12 grid gap-8 md:grid-cols-2">
    <div>
        <h2 class="label">{{ __('ui.content.related') }}</h2>
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($tools as $tool)
                <li><a href="{{ lroute($tool) }}" class="btn btn-secondary">{{ __('ui.pages.'.$tool) }}</a></li>
            @endforeach
        </ul>
    </div>
    <div>
        <h2 class="label">{{ __('ui.content.software_pages') }}</h2>
        <ul class="mt-3 flex flex-wrap gap-2">
            @foreach ($apps as $slug => $name)
                <li><a href="{{ lroute('fix.software', ['software' => $slug]) }}" class="btn btn-secondary" dir="ltr">{{ $name }}</a></li>
            @endforeach
        </ul>
    </div>
</section>
