<x-mail-layout>
    <p style="font-size:16px">{{ __('ui.auth.mail_intro') }}</p>
    <p style="font-size:34px;letter-spacing:6px;font-weight:bold;margin:24px 0" dir="ltr">{{ $code }}</p>
    <p style="font-size:14px;color:#6B6680">{{ __('ui.auth.mail_expiry', ['minutes' => \App\Http\Controllers\AuthController::CODE_TTL_MINUTES]) }}</p>
</x-mail-layout>
