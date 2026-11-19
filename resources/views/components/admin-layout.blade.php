<x-layouts.app :title="$title.' | Admin'" description="Admin" :noindex="true">
    <div class="pt-8">
        <nav class="flex flex-wrap gap-2 border-b border-line pb-4" aria-label="Admin">
            @foreach (['dashboard' => __('ui.admin.dashboard'), 'orders' => __('ui.admin.orders'), 'users' => __('ui.admin.users'), 'settings' => __('ui.admin.settings'), 'log' => __('ui.admin.log')] as $r => $label)
                <a href="{{ route('admin.'.$r) }}" @class(['chip no-underline', 'bg-lav-100 border-lav-200' => request()->routeIs('admin.'.$r) || request()->routeIs('admin.'.$r.'.*')])
                   @if (request()->routeIs('admin.'.$r)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        @if (session('status'))
            <p class="mt-4 rounded-panel bg-lav-100 border border-lav-200 p-3 text-small" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <p class="mt-4 rounded-panel border border-warn/30 bg-surface p-3 text-small text-warn" role="alert">{{ $errors->first() }}</p>
        @endif
        <h1 class="mt-6 text-h3 font-semibold">{{ $title }}</h1>
        <div class="mt-4">{{ $slot }}</div>
    </div>
</x-layouts.app>
