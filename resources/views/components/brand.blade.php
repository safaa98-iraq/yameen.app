{{-- Wordmark "يمين" (Reem Kufi outlines) with a left arrow: writing starts from the right. --}}
@props(['class' => 'h-8 w-auto'])
<span {{ $attributes->merge(['class' => 'inline-flex text-lav-700']) }}>
    <svg class="{{ $class }}" viewBox="-370 -500 1900 740" aria-hidden="true" focusable="false">
        {!! preg_replace('/^<svg[^>]*>|<\/svg>\s*$/', '', file_get_contents(resource_path('svg/wordmark.svg'))) !!}
    </svg>
</span>
