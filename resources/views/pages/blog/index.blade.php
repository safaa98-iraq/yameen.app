<x-layouts.app :title="__('ui.blog.title')" :description="__('ui.blog.description')"
               :breadcrumbs="[[__('ui.blog.h1'), lroute('blog')]]">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.blog.h1')" :intro="__('ui.blog.intro')" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8">
        @forelse ($posts as $post)
            <article class="border-b border-line py-6">
                <h2 class="text-lead font-semibold">
                    <a href="{{ lroute('blog.show', ['slug' => basename($post->slug)]) }}" class="text-ink no-underline hover:underline">{{ $post->h1 }}</a>
                </h2>
                <p class="mt-1 text-muted">{{ $post->description }}</p>
                @if ($post->meta('date'))
                    <p class="mt-2 text-meta text-muted tabular-nums"><time datetime="{{ $post->meta('date') }}">{{ $post->meta('date') }}</time></p>
                @endif
            </article>
        @empty
            <p class="text-muted">{{ __('ui.blog.empty') }}</p>
        @endforelse
    </div>
</x-layouts.app>
