@php
    $groups = [
        'tools' => ['fix', 'pdf-fix', 'clean', 'export'],
        'fonts' => ['fonts', 'fonts-compare' => 'fonts.compare', 'fonts-pairing' => 'fonts.pairing'],
        'site' => ['about', 'pricing', 'privacy', 'terms', 'contact'],
    ];
@endphp
<footer class="mt-18 border-t border-line">
    <div class="max-w-content mx-auto px-4 md:px-6 py-12 grid gap-8 md:grid-cols-[1.5fr_1fr_1fr_1fr]">
        <div>
            <x-brand class="h-7 w-auto" />
            <p class="mt-3 text-small text-muted max-w-xs">{{ __('ui.tagline') }}</p>
        </div>
        @foreach ($groups as $group => $links)
            <div>
                <h2 class="label">{{ __('ui.footer.'.$group) }}</h2>
                <ul class="mt-3 space-y-1">
                    @foreach ($links as $label => $route)
                        @php $label = is_string($label) ? $label : $route; @endphp
                        <li><a href="{{ lroute($route) }}" class="inline-flex min-h-8 items-center text-small text-ink no-underline hover:underline">{{ __('ui.pages.'.$label) }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="max-w-content mx-auto px-4 md:px-6">
        <div class="py-6 border-t border-line text-meta text-muted space-y-1">
        <p>{{ __('ui.footer.fonts_credit') }} {{ __('ui.footer.ui_fonts') }}</p>
        <p class="tabular-nums">{{ __('ui.footer.copyright', ['year' => now()->year]) }}</p>
        </div>
    </div>
</footer>
