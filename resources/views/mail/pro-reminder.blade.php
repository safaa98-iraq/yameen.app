<x-mail-layout>
    <p style="font-size:16px">{{ __('ui.reminder.body', ['date' => $date]) }}</p>
    <p style="margin:24px 0"><a href="{{ $url }}" style="background:#5B4A9E;color:#fff;padding:12px 20px;border-radius:6px;text-decoration:none;display:inline-block">{{ __('ui.reminder.button') }}</a></p>
    <p style="font-size:14px;color:#6B6680">{{ __('ui.reminder.note') }}</p>
</x-mail-layout>
