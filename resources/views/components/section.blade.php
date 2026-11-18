{{-- Numbered content section in the specimen-sheet style: "01  Title", 1px rule above. --}}
@props(['num', 'title', 'id' => null])
<section {{ $attributes->merge(['class' => 'border-t border-line py-12 grid gap-4 md:grid-cols-[6rem_1fr]']) }}
         @if ($id) id="{{ $id }}" @endif>
    <div class="section-num text-h3 leading-none pt-1" aria-hidden="true">{{ $num }}</div>
    <div>
        <h2 class="text-h3 font-semibold leading-snug">{{ $title }}</h2>
        <div class="mt-4 max-w-prose prose-ui">{{ $slot }}</div>
    </div>
</section>
