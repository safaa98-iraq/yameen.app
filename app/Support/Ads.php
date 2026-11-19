<?php

namespace App\Support;

use App\Models\Setting;

class Ads
{
    /** Ads show only when enabled with a client ID, and never to Pro users. */
    public static function enabled(): bool
    {
        return (bool) Setting::get('ads_enabled')
            && filled(Setting::get('adsense_client'))
            && ! auth()->user()?->isPro();
    }

    public static function slot(string $name): ?string
    {
        $id = Setting::get('ad_slot_'.$name);

        return filled($id) ? (string) $id : null;
    }
}
