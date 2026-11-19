@php $title = __('ui.pages.'.$page); @endphp
<x-layouts.app :title="$title.' | '.__('ui.brand')" :noindex="true">
    <div class="py-18 max-w-prose">
        <h1 class="text-h2 font-semibold leading-tight">{{ $title }}</h1>
        <p class="mt-4 text-muted">{{ __('ui.placeholder.body') }}</p>
        <a href="{{ lroute('home') }}" class="btn btn-secondary mt-8">{{ __('ui.placeholder.back') }}</a>
    </div>
</x-layouts.app>
