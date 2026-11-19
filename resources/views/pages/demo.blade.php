<x-layouts.app :title="__('ui.demo.title')" :description="__('ui.demo.title')" :noindex="true">
    <x-slot:hero>
        <x-page-hero :h1="__('ui.demo.h1')" :intro="__('ui.demo.intro')" />
    </x-slot:hero>

    <div class="max-w-2xl mx-auto pt-8 grid gap-3 sm:grid-cols-2">
        @foreach ($accounts as $role => $email)
            <form method="POST" action="{{ route('demo.login', $role) }}" class="rounded-panel border border-line bg-surface p-5 flex flex-col">
                @csrf
                <h2 class="font-semibold">{{ __('ui.demo.'.$role) }}</h2>
                <p class="mt-1 text-small text-muted flex-1">{{ __('ui.demo.'.$role.'_note') }}</p>
                <p class="mt-2 text-meta text-muted" dir="ltr">{{ $email }}</p>
                <button class="btn {{ $role === 'admin' ? 'btn-primary' : 'btn-secondary' }} mt-4">{{ __('ui.demo.enter') }}</button>
            </form>
        @endforeach
        <p class="sm:col-span-2 text-meta text-muted">{{ __('ui.demo.warning') }}</p>
    </div>
</x-layouts.app>
