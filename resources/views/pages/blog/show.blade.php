@php
    $url = lroute('blog.show', ['slug' => $slug]);
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $page->h1,
        'description' => $page->description,
        'inLanguage' => app()->getLocale(),
        'mainEntityOfPage' => $url,
        'datePublished' => (string) $page->meta('date'),
        'publisher' => ['@type' => 'Organization', 'name' => __('ui.brand'), 'url' => lroute('home')],
    ];
@endphp
<x-layouts.app :title="$page->title" :description="$page->description" type="article" :schema="[$article]"
               :breadcrumbs="[[__('ui.blog.h1'), lroute('blog')], [$page->h1, $url]]">
    <x-slot:hero>
        <x-page-hero :h1="$page->h1" :intro="$page->intro" />
    </x-slot:hero>

    <div class="pt-8 xl:grid xl:grid-cols-[1fr_300px] xl:gap-10">
        <article>
            @if ($page->meta('date'))
                <p class="max-w-3xl mx-auto text-meta text-muted tabular-nums"><time datetime="{{ $page->meta('date') }}">{{ $page->meta('date') }}</time></p>
            @endif
            <x-prose-body :page="$page" />
        </article>
        {{-- Sidebar ad: desktop only. --}}
        <div class="hidden xl:block"><x-ad placement="sidebar" class="sticky top-24 my-0" :height="600" /></div>
    </div>

    <div class="max-w-3xl mx-auto mt-12 rounded-panel bg-lav-50 border border-line p-5 md:p-6">
        <p class="font-semibold">{{ __('ui.blog.cta_title') }}</p>
        <p class="mt-1 text-small text-muted">{{ __('ui.blog.cta_body') }}</p>
        <a href="{{ lroute('home') }}" class="btn btn-primary mt-4">{{ __('ui.blog.cta_button') }}</a>
    </div>
</x-layouts.app>
