@php
    use App\Support\Locales;
    $other = Locales::current() === 'ar' ? 'en' : 'ar';
    $nav = [
        ['fix', 'fix*'],
        ['fonts', 'fonts*'],
        ['export', 'export'],
        ['pricing', 'pricing'],
    ];
@endphp
<header class="border-b border-line bg-paper" x-data="{ open: false }">
    <div class="max-w-content mx-auto px-4 md:px-6 h-16 flex items-center gap-6">
        <a href="{{ lroute('home') }}" class="shrink-0 -my-2 py-2" aria-label="{{ __('ui.brand') }}">
            <x-brand class="h-8 w-auto" />
        </a>

        <nav aria-label="{{ __('ui.nav.label') }}" class="hidden md:block">
            <ul class="flex items-center gap-1">
                @foreach ($nav as [$key, $pattern])
                    <li>
                        <a href="{{ lroute($key) }}"
                           @class([
                               'inline-flex items-center min-h-11 px-3 rounded-control text-small no-underline transition-colors',
                               'text-ink font-medium bg-lav-50' => route_is($pattern),
                               'text-muted hover:text-ink hover:bg-lav-50' => ! route_is($pattern),
                           ])
                           @if (route_is($pattern)) aria-current="page" @endif>{{ __('ui.nav.'.$key) }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="ms-auto flex items-center gap-4">
            <x-credits-counter class="hidden lg:block" />
            <a href="{{ Locales::switchUrl($other, true) }}" hreflang="{{ $other }}" lang="{{ $other }}"
               class="hidden md:inline-flex items-center min-h-11 px-2 text-small text-muted hover:text-ink no-underline"
               title="{{ __('ui.lang_switch_label') }}">{{ __('ui.lang_switch') }}</a>
            <a href="{{ lroute('login') }}" class="btn btn-secondary">{{ __('ui.nav.login') }}</a>
            <button type="button" class="md:hidden btn btn-secondary px-3"
                    @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-nav">
                <span x-text="open ? '{{ __('ui.nav.close') }}' : '{{ __('ui.nav.menu') }}'">{{ __('ui.nav.menu') }}</span>
            </button>
        </div>
    </div>

    <div id="mobile-nav" class="md:hidden border-t border-line" x-show="open" x-cloak>
        <nav aria-label="{{ __('ui.nav.label') }}" class="px-4 py-2">
            <ul>
                @foreach ($nav as [$key, $pattern])
                    <li><a href="{{ lroute($key) }}" class="flex items-center min-h-11 text-ink no-underline"
                           @if (route_is($pattern)) aria-current="page" @endif>{{ __('ui.nav.'.$key) }}</a></li>
                @endforeach
                <li><a href="{{ Locales::switchUrl($other, true) }}" hreflang="{{ $other }}" lang="{{ $other }}"
                       class="flex items-center min-h-11 text-muted no-underline">{{ __('ui.lang_switch') }}</a></li>
            </ul>
            <x-credits-counter class="py-3 border-t border-line" />
        </nav>
    </div>
</header>
