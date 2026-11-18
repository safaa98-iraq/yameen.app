@props(['h1', 'intro' => null])
<div class="pt-8 md:pt-12 pb-8 max-w-3xl">
    <h1 class="text-h3 md:text-h2 font-semibold leading-tight">{{ $h1 }}</h1>
    @if ($intro)
        <p class="mt-3 text-muted">{{ $intro }}</p>
    @endif
</div>
