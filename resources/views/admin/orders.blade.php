<x-admin-layout :title="__('ui.admin.orders')">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        @foreach (['' => __('ui.fonts.all_styles'), 'pending' => 'pending', 'paid' => 'paid', 'failed' => 'failed', 'refunded' => 'refunded'] as $value => $label)
            <button name="status" value="{{ $value }}" @class(['chip', 'bg-lav-100 border-lav-200' => ($status ?? '') === $value])>{{ $label }}</button>
        @endforeach
    </form>
    @include('admin.partials.orders-table', ['orders' => $orders])
    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
