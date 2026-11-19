<x-layouts.app :title="$title.' | Admin'" description="Admin" :noindex="true">
    <div class="pt-8">
        <nav class="flex flex-wrap gap-2 border-b border-line pb-4" aria-label="Admin">
            @foreach (['orders' => __('ui.admin.orders'), 'users' => __('ui.admin.users'), 'settings' => __('ui.admin.settings')] as $r => $label)
                <a href="{{ route('admin.'.$r) }}" @class(['chip no-underline', 'bg-lav-100 border-lav-200' => request()->routeIs('admin.'.$r)])>{{ $label }}</a>
            @endforeach
        </nav>
        @if (session('status'))
            <p class="mt-4 rounded-panel bg-lav-100 border border-lav-200 p-3 text-small" role="status">{{ session('status') }}</p>
        @endif
        <h1 class="mt-6 text-h3 font-semibold">{{ $title }}</h1>
        <div class="mt-4">{{ $slot }}</div>
    </div>
</x-layouts.app>
