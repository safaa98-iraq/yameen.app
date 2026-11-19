<x-layouts.app :title="$page->title" :description="$page->description" :app="true"
               :breadcrumbs="[[__('ui.pages.garbled'), lroute('garbled')]]">
    <x-slot:hero>
        <x-page-hero :h1="$page->h1" :intro="$page->intro" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8">
        <x-tool.garbled />
    </div>

    <x-ad placement="tool" class="max-w-3xl mx-auto mt-8" />

    <div class="mt-18">
        <x-content-body :page="$page" />
        <x-related-tools except="garbled" />
    </div>
</x-layouts.app>
