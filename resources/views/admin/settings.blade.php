<x-admin-layout :title="__('ui.admin.settings')">
    <form method="POST" action="{{ route('admin.settings.save') }}" class="max-w-xl space-y-4 rounded-panel border border-line bg-surface p-5">
        @csrf
        @foreach ($values as $key => $value)
            @if ($key === 'ads_enabled')
                <label class="flex items-center gap-2 min-h-11"><input type="checkbox" name="ads_enabled" value="1" @checked($value)> {{ __('ui.admin.s_'.$key) }}</label>
            @else
                <label class="block">
                    <span class="label">{{ __('ui.admin.s_'.$key) }}</span>
                    <input name="{{ $key }}" value="{{ old($key, $value) }}" class="field mt-1 h-11 px-3" dir="ltr"
                           @if (! in_array($key, ['adsense_client', 'ad_slot_tool', 'ad_slot_grid', 'ad_slot_sidebar'])) type="number" step="{{ $key === 'usd_to_iqd_rate' ? '0.01' : '1' }}" @endif>
                    @error($key) <span class="text-meta text-warn">{{ $message }}</span> @enderror
                </label>
            @endif
        @endforeach
        <button class="btn btn-primary">{{ __('ui.admin.save') }}</button>
    </form>
</x-admin-layout>
