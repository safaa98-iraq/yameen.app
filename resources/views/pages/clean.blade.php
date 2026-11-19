<x-layouts.app :title="$page->title" :description="$page->description" :app="true"
               :breadcrumbs="[[__('ui.pages.clean'), lroute('clean')]]">
    <x-slot:hero>
        <x-page-hero :h1="$page->h1" :intro="$page->intro" />
    </x-slot:hero>

    <div class="max-w-3xl mx-auto pt-8">

    <x-tool.clean />

    </div>

    <div class="mt-18">
        <x-content-body :page="$page" />
        <x-related-tools except="clean" />
    </div>
</x-layouts.app>
