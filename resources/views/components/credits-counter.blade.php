{{-- Quiet credits text, filled from /api/credits. Hidden where there is no API (static preview). --}}
<p {{ $attributes->merge(['class' => 'text-meta text-muted tabular-nums']) }} data-credits
   x-data x-show="$store.credits.available" x-cloak
   x-text="$store.credits.pro ? @js(__('ui.credits_ui.pro')) : @js(__('ui.credits_ui.counter')).replace(':remaining', $store.credits.remaining).replace(':limit', $store.credits.limit)"></p>
