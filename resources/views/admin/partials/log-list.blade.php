<ul class="mt-3 divide-y divide-line rounded-panel border border-line bg-surface text-small">
    @foreach ($actions as $a)
        <li class="grid gap-1 p-3 sm:grid-cols-[10rem_1fr_auto] sm:items-baseline">
            <span class="text-meta text-muted tabular-nums">{{ $a->created_at->format('Y-m-d H:i') }}</span>
            <span><span class="font-medium">{{ __('ui.admin.a_'.str_replace('.', '_', $a->action)) }}</span>
                <span class="text-muted" dir="ltr">{{ $a->subject }}</span>
                @if ($a->details) <span class="text-meta text-muted" dir="ltr">{{ json_encode($a->details, JSON_UNESCAPED_UNICODE) }}</span> @endif
            </span>
            <span class="text-meta text-muted" dir="ltr">{{ $a->admin?->email }}</span>
        </li>
    @endforeach
</ul>
