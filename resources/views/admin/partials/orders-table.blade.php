<div class="mt-3">
<div class="overflow-x-auto rounded-panel border border-line bg-surface">
        <table class="w-full text-small">
            <thead class="text-muted"><tr class="border-b border-line">
                <th class="p-3 text-start">ID</th><th class="p-3 text-start">{{ __('ui.admin.user') }}</th><th class="p-3 text-start">{{ __('ui.account.plan') }}</th>
                <th class="p-3 text-start">IQD</th><th class="p-3 text-start">{{ __('ui.admin.rate') }}</th><th class="p-3 text-start">{{ __('ui.account.status') }}</th>
                <th class="p-3 text-start">{{ __('ui.account.date') }}</th><th class="p-3"></th>
            </tr></thead>
            <tbody>
            @forelse ($orders as $order)
                <tr class="border-b border-line align-top">
                    <td class="p-3 font-mono text-meta" dir="ltr">{{ $order->id }}</td>
                    <td class="p-3" dir="ltr">@if ($order->user)<a href="{{ route('admin.users.show', $order->user) }}">{{ $order->user->email }}</a>@endif</td>
                    <td class="p-3">{{ $order->plan }} ({{ $order->days }})</td>
                    <td class="p-3 tabular-nums">{{ number_format($order->amount_iqd) }}@if ($order->refunded_iqd) <span class="text-warn">−{{ number_format($order->refunded_iqd) }}</span>@endif</td>
                    <td class="p-3 tabular-nums">{{ $order->rate_used }}</td>
                    <td class="p-3">{{ $order->status }}</td>
                    <td class="p-3 tabular-nums">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td class="p-3 min-w-64">
                        @if ($order->status === 'pending')
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.orders.paid', $order) }}">@csrf<button class="btn btn-secondary">{{ __('ui.admin.mark_paid') }}</button></form>
                                <form method="POST" action="{{ route('admin.orders.failed', $order) }}">@csrf<button class="btn btn-secondary">{{ __('ui.admin.mark_failed') }}</button></form>
                            </div>
                        @endif
                        @if ($order->status === 'paid')
                            <details @if ($errors->has('refund_'.$order->id) || $errors->has('reason')) open @endif>
                                <summary class="cursor-pointer text-warn">{{ __('ui.admin.refund') }}</summary>
                                <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="mt-2 space-y-2"
                                      x-data="{ reason: @js(old('reason', '')) }">
                                    @csrf
                                    <label class="block text-meta">{{ __('ui.admin.amount_iqd') }}
                                        <input name="amount" type="number" min="1" max="{{ $order->amount_iqd - $order->refunded_iqd }}" value="{{ $order->amount_iqd - $order->refunded_iqd }}" required class="field h-10 px-2 mt-1" dir="ltr">
                                    </label>
                                    <label class="block text-meta">{{ __('ui.admin.reason') }}
                                        <textarea name="reason" x-model="reason" minlength="100" maxlength="1500" required rows="3" class="field p-2 mt-1"></textarea>
                                    </label>
                                    <p class="text-meta" :class="reason.length < 100 || reason.length > 1500 ? 'text-warn' : 'text-muted'" x-text="reason.length + ' / 100–1500'"></p>
                                    @error('refund_'.$order->id) <p class="text-meta text-warn">{{ $message }}</p> @enderror
                                    @error('reason') <p class="text-meta text-warn">{{ $message }}</p> @enderror
                                    <button class="btn btn-secondary" :disabled="reason.length < 100 || reason.length > 1500">{{ __('ui.admin.refund') }}</button>
                                </form>
                            </details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="p-4 text-muted">{{ __('ui.account.no_orders') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
