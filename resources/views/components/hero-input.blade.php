{{-- Home hero: short explanation, then the app picker. --}}
@props(['h1', 'lead' => null])
<section class="bg-lav-50 border-b border-line">
    <div class="max-w-3xl mx-auto px-4 pt-10 pb-10 md:pt-14 md:pb-14">
        <div class="text-center">
            <h1 class="text-h3 md:text-h1 font-semibold leading-tight">{{ $h1 }}</h1>
            @if ($lead)
                <p class="mt-3 text-body md:text-lead text-muted">{{ $lead }}</p>
            @endif
        </div>
        <div class="mt-8">
            <x-app-picker />
        </div>
    </div>
</section>
