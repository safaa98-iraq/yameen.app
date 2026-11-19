@php $rtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#FAF9FC;font-family:Tahoma,Arial,sans-serif;color:#1E1A2B">
<div style="max-width:520px;margin:0 auto;padding:32px 24px;text-align:{{ $rtl ? 'right' : 'left' }}">
    <p style="font-size:22px;font-weight:bold;color:#5B4A9E;margin:0 0 24px">{{ __('ui.brand') }}</p>
    {{ $slot }}
    <p style="font-size:13px;color:#6B6680;margin-top:32px">yameen.app</p>
</div>
</body>
</html>
