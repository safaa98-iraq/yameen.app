<x-layouts.app :title="$page->title" :description="$page->description">
    <x-page-intro :h1="$page->h1" :intro="$page->intro" />

    <x-tool.clean />

    <div class="mt-18">
        <x-content-body :page="$page" />
        <x-related-tools except="clean" />
    </div>
</x-layouts.app>
