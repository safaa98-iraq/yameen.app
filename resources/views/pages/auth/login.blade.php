<x-layouts.app :title="__('ui.auth.title')" :description="__('ui.auth.description')" :noindex="true">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.auth.h1')" :intro="__('ui.auth.intro')" />
    </x-slot:hero>

    <div class="max-w-md mx-auto pt-8">
        <div class="rounded-panel border border-line bg-surface p-5 md:p-6">
            @if (session('status'))
                <p class="mb-4 text-small text-lav-700" role="status">{{ session('status') }}</p>
            @endif

            @if ($email)
                <form method="POST" action="{{ lroute('login.verify') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <div>
                        <label for="code" class="block font-semibold">{{ __('ui.auth.code_label') }}</label>
                        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus
                               class="field mt-2 h-12 px-4 text-h3 tracking-[0.4em] text-center" dir="ltr">
                        @error('code') <p class="mt-2 text-small text-warn" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn btn-primary btn-primary-lg w-full">{{ __('ui.auth.verify') }}</button>
                </form>
                <form method="POST" action="{{ lroute('login.send') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button class="text-small text-lav-700 underline min-h-11">{{ __('ui.auth.resend') }}</button>
                </form>
            @else
                <form method="POST" action="{{ lroute('login.send') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block font-semibold">{{ __('ui.auth.email_label') }}</label>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                               class="field mt-2 h-12 px-4" dir="ltr">
                        @error('email') <p class="mt-2 text-small text-warn" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn btn-primary btn-primary-lg w-full">{{ __('ui.auth.send') }}</button>
                </form>
            @endif

            @if ($google)
                <div class="my-5 flex items-center gap-3 text-meta text-muted"><span class="flex-1 border-t border-line"></span>{{ __('ui.auth.or') }}<span class="flex-1 border-t border-line"></span></div>
                <a href="{{ route('auth.google', ['locale' => app()->getLocale()]) }}" class="btn btn-secondary btn-primary-lg w-full">{{ __('ui.auth.google') }}</a>
            @endif
        </div>
        <p class="mt-4 text-meta text-muted text-center">{{ __('ui.auth.no_password') }}</p>
        @if (config('yameen.demo'))
            <p class="mt-2 text-small text-center"><a href="{{ route('demo') }}">{{ __('ui.demo.link') }}</a></p>
        @endif
    </div>
</x-layouts.app>
