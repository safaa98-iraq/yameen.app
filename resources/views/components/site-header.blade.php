@php
    use App\Support\Locales;
    $other = Locales::current() === 'ar' ? 'en' : 'ar';
    // Only pages that work today. Export, pricing and login return when built.
    $nav = [
        ['fix', 'fix*', 'nav.fix'],
        ['fonts', 'fonts*', 'nav.fonts'],
        ['pdf-fix', 'pdf-fix', 'nav.pdf'],
        ['garbled', 'garbled', 'nav.garbled'],
        ['clean', 'clean', 'nav.clean'],
    ];
@endphp
<header class="sticky top-0 z-40 border-b border-line bg-surface" x-data="{ open: false }">
    <div class="max-w-content mx-auto px-4 md:px-6 h-16 flex items-center gap-8">
        <a href="{{ lroute('home') }}" class="shrink-0 -my-2 py-2" aria-label="{{ __('ui.brand') }}">
            <x-brand class="h-8 w-auto" />
        </a>

        <nav aria-label="{{ __('ui.nav.label') }}" class="hidden md:block">
            <ul class="flex items-center gap-1">
                @foreach ($nav as [$route, $pattern, $label])
                    <li>
                        <a href="{{ lroute($route) }}"
                           @class([
                               'inline-flex items-center min-h-11 px-3 rounded-control text-small font-medium no-underline transition-colors',
                               'text-lav-700 bg-lav-50' => route_is($pattern),
                               'text-ink hover:bg-lav-50' => ! route_is($pattern),
                           ])
                           @if (route_is($pattern)) aria-current="page" @endif>{{ __('ui.'.$label) }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ms-auto flex items-center gap-2">
            <a href="{{ Locales::switchUrl($other, true) }}" hreflang="{{ $other }}" lang="{{ $other }}"
               class="inline-flex items-center min-h-11 px-3 rounded-control text-small text-muted hover:text-ink hover:bg-lav-50 no-underline"
               title="{{ __('ui.lang_switch_label') }}">{{ __('ui.lang_switch') }}</a>
            <button type="button" class="md:hidden btn btn-secondary px-3"
                    @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav">
                <span x-text="open ? @js(__('ui.nav.close')) : @js(__('ui.nav.menu'))">{{ __('ui.nav.menu') }}</span>
            </button>
        </div>
    </div>

    <div id="mobile-nav" class="md:hidden border-t border-line bg-surface" x-show="open" x-cloak>
        <nav aria-label="{{ __('ui.nav.label') }}" class="px-4 py-2">
            <ul>
                @foreach ($nav as [$route, $pattern, $label])
                    <li><a href="{{ lroute($route) }}" class="flex items-center min-h-12 text-ink font-medium no-underline border-b border-line last:border-0"
                           @if (route_is($pattern)) aria-current="page" @endif>{{ __('ui.'.$label) }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>
</header>
