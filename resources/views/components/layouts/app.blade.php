@props([
    'title' => __('ui.home.title'),
    'description' => __('ui.home.description'),
])
@php
    use App\Support\Locales;
    $locale = Locales::current();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ Locales::dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#FAF9FC">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @foreach (Locales::all() as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ Locales::switchUrl($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ Locales::switchUrl(config('yameen.default_locale')) }}">
    {{ $head ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:start-2 focus:z-50 btn btn-secondary">{{ __('ui.skip_to_content') }}</a>

    <x-site-header />

    <main id="main" class="flex-1 w-full max-w-content mx-auto px-4 md:px-6">
        {{ $slot }}
    </main>

    <x-site-footer />
</body>
</html>
