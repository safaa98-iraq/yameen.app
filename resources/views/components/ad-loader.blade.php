{{--
    Loads AdSense after the first interaction or 3 seconds, so it never delays
    the tool. Google's Privacy & messaging (configured in AdSense) shows the
    consent form to EEA/UK visitors before personalised ads.
--}}
@if (\App\Support\Ads::enabled())
<script>
(() => {
    let done = false;
    const load = () => {
        if (done) return;
        done = true;
        const s = document.createElement('script');
        s.async = true;
        s.crossOrigin = 'anonymous';
        s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ \App\Models\Setting::get('adsense_client') }}';
        s.onload = () => document.querySelectorAll('ins.adsbygoogle').forEach(() => (window.adsbygoogle = window.adsbygoogle || []).push({}));
        document.head.appendChild(s);
    };
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach((e) => addEventListener(e, load, { once: true, passive: true }));
    setTimeout(load, 3000);
})();
</script>
@endif
