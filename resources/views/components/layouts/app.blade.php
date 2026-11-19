@props([
    'title' => __('ui.home.title'),
    'description' => __('ui.home.description'),
    // [[label, url], ...] after Home; the current page is the last item. Empty on the home page.
    'breadcrumbs' => [],
    // Adds WebApplication structured data (tool pages).
    'app' => false,
    'image' => null,
    'type' => 'website',
    'schema' => [],
    'noindex' => false,
])
@php
    use App\Support\Locales;
    use App\Support\Seo;
    $locale = Locales::current();
    $canonical = url()->current();
    $image = $image ?? Seo::defaultImage();
    $graph = array_merge(
        $breadcrumbs ? [Seo::breadcrumbs($breadcrumbs)] : [Seo::website()],
        $app ? [Seo::webApplication(Seo::shortTitle($title), $description, $canonical)] : [],
        $schema,
    );
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ Locales::dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($noindex || Seo::noindex())
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ __('ui.brand') }}">
    <meta property="og:title" content="{{ Seo::shortTitle($title) }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ Locales::all()[$locale]['og'] }}">
    @foreach (Locales::all() as $code => $meta)
        @if ($code !== $locale)
            <meta property="og:locale:alternate" content="{{ $meta['og'] }}">
        @endif
    @endforeach
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#FAF9FC">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @foreach (Locales::all() as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ Locales::switchUrl($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ Locales::switchUrl(config('yameen.default_locale')) }}">
    @foreach ($graph as $data)
        <script type="application/ld+json">{!! Seo::json($data) !!}</script>
    @endforeach
    {{ $head ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:start-2 focus:z-50 btn btn-secondary">{{ __('ui.skip_to_content') }}</a>

    <x-site-header />

    <main id="main" class="flex-1 w-full">
        {{ $hero ?? '' }}
        <div class="max-w-content mx-auto px-4 md:px-6">
            {{ $slot }}
        </div>
    </main>

    <x-site-footer />
</body>
</html>
