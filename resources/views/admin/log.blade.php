<x-admin-layout :title="__('ui.admin.log')">
    @if ($actions->isEmpty())
        <p class="text-muted">{{ __('ui.admin.no_actions') }}</p>
    @else
        @include('admin.partials.log-list', ['actions' => $actions])
        <div class="mt-4">{{ $actions->links() }}</div>
    @endif
</x-admin-layout>
