<x-layouts.app :title="$page->title" :description="$page->description"
               :breadcrumbs="[[$page->h1, lroute($slug)]]">
    <x-slot:hero>
        <x-page-hero :h1="$page->h1" :intro="$page->intro" />
    </x-slot:hero>

    <div class="pt-8">
        @if ($slug === 'contact')
            <div class="max-w-3xl mx-auto mb-6 rounded-panel border border-line bg-surface p-5 md:p-6">
                <p class="text-small text-muted">{{ __('ui.static.email_label') }}</p>
                <a href="mailto:{{ config('yameen.contact_email') }}" class="mt-1 inline-block text-lead font-semibold" dir="ltr">{{ config('yameen.contact_email') }}</a>
            </div>
        @endif
        <x-prose-body :page="$page" />
        @if ($page->meta('updated'))
            <p class="max-w-3xl mx-auto mt-8 text-meta text-muted">{{ __('ui.static.updated', ['date' => $page->meta('updated')]) }}</p>
        @endif
    </div>
</x-layouts.app>
