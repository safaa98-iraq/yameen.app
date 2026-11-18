{{-- Quiet credits text. Values are placeholders until the credits service lands (phase 4). --}}
@props(['remaining' => 8, 'limit' => 8])
<p {{ $attributes->merge(['class' => 'text-meta text-muted tabular-nums']) }} data-credits>
    {{ __('ui.credits.counter', ['remaining' => $remaining, 'limit' => $limit]) }}
</p>
