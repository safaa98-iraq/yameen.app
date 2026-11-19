{{--
    AdSense slot. Renders only when ads are enabled and the user isn't Pro.
    Space is reserved up front so the ad never shifts the layout (CLS).
    Never placed above or over the text input.
--}}
@props(['placement', 'height' => 280])
@if (\App\Support\Ads::enabled() && ($unit = \App\Support\Ads::slot($placement)))
    <aside {{ $attributes->merge(['class' => 'my-8']) }} aria-label="{{ __('ui.admin.ad_label') }}">
        <p class="text-meta text-muted mb-1">{{ __('ui.admin.ad_label') }}</p>
        <div class="overflow-hidden rounded-panel bg-lav-50" style="min-height: {{ (int) $height }}px">
            <ins class="adsbygoogle" style="display:block; min-height: {{ (int) $height }}px"
                 data-ad-client="{{ \App\Models\Setting::get('adsense_client') }}"
                 data-ad-slot="{{ $unit }}" data-ad-format="auto" data-full-width-responsive="true"></ins>
        </div>
    </aside>
@endif
