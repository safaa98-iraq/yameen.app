@props(['h1', 'intro' => null])
<section class="bg-lav-50 border-b border-line">
    <div class="max-w-3xl mx-auto px-4 py-10 md:py-12 text-center">
        <h1 class="text-h3 md:text-h2 font-semibold leading-tight">{{ $h1 }}</h1>
        @if ($intro)
            <p class="mt-3 text-muted">{{ $intro }}</p>
        @endif
    </div>
</section>
