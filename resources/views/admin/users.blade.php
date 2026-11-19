<x-admin-layout :title="__('ui.admin.users')">
    <form method="GET" class="flex gap-2 mb-4">
        <label for="q" class="sr-only">{{ __('ui.auth.email_label') }}</label>
        <input id="q" name="q" value="{{ $q }}" placeholder="{{ __('ui.auth.email_label') }}" class="field h-11 px-3 max-w-sm" dir="ltr">
        <button class="btn btn-secondary">{{ __('ui.admin.search') }}</button>
    </form>
    <div class="overflow-x-auto rounded-panel border border-line bg-surface">
        <table class="w-full text-small">
            <thead class="text-muted"><tr class="border-b border-line">
                <th class="p-3 text-start">{{ __('ui.auth.email_label') }}</th><th class="p-3 text-start">Pro</th><th class="p-3 text-start">Admin</th><th class="p-3 text-start">{{ __('ui.account.date') }}</th><th class="p-3"></th>
            </tr></thead>
            <tbody>
            @foreach ($users as $user)
                <tr class="border-b border-line align-top">
                    <td class="p-3" dir="ltr">{{ $user->email }}</td>
                    <td class="p-3 tabular-nums">{{ $user->isPro() ? $user->pro_until->format('Y-m-d') : '—' }}</td>
                    <td class="p-3">{{ $user->is_admin ? '✓' : '' }}</td>
                    <td class="p-3 tabular-nums">{{ $user->created_at->format('Y-m-d') }}</td>
                    <td class="p-3">
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.users.pro', $user) }}" class="flex gap-1">
                                @csrf
                                <label class="sr-only" for="days-{{ $user->id }}">{{ __('ui.admin.days') }}</label>
                                <input id="days-{{ $user->id }}" name="days" type="number" min="1" value="30" class="field h-10 w-20 px-2" dir="ltr">
                                <button class="btn btn-secondary">{{ __('ui.admin.grant') }}</button>
                            </form>
                            @if ($user->isPro())
                                <form method="POST" action="{{ route('admin.users.revoke', $user) }}">@csrf<button class="btn btn-secondary">{{ __('ui.admin.revoke') }}</button></form>
                            @endif
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.admin', $user) }}">@csrf<button class="btn btn-secondary">{{ $user->is_admin ? __('ui.admin.remove_admin') : __('ui.admin.make_admin') }}</button></form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
