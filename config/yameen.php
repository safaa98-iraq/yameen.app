<?php

return [
    // Supported UI locales. The first one is served at the site root.
    'locales' => [
        'ar' => ['prefix' => '', 'dir' => 'rtl', 'name' => 'العربية', 'short' => 'ع', 'og' => 'ar_IQ'],
        'en' => ['prefix' => 'en', 'dir' => 'ltr', 'name' => 'English', 'short' => 'EN', 'og' => 'en_US'],
    ],

    'default_locale' => 'ar',

    // Timezone used for the daily credit reset.
    'credits_timezone' => 'Asia/Baghdad',

    // Software pages under /fix/{software}. Order is display order.
    'software' => [
        'photoshop' => 'Photoshop',
        'after-effects' => 'After Effects',
        'premiere' => 'Premiere Pro',
        'illustrator' => 'Illustrator',
        'unity' => 'Unity',
        'blender' => 'Blender',
        'cinema-4d' => 'Cinema 4D',
    ],

    'seo' => [
        // Set on preview deployments so search engines do not index them.
        'noindex' => (bool) env('SEO_NOINDEX', false),
    ],

    // Defaults for admin-editable settings (App\Models\Setting overrides them).
    'settings' => [
        'credits_visitor' => 8,
        'credits_free' => 15,
        'cost_fix' => 1,
        'cost_preview' => 1,
        'cost_png' => 2,
        'usd_to_iqd_rate' => 1310,
        'ads_enabled' => false,
        'adsense_client' => '',
        // AdSense ad unit IDs per placement (data-ad-slot).
        'ad_slot_tool' => '',
        'ad_slot_grid' => '',
        'ad_slot_sidebar' => '',
    ],

    'plans' => [
        'monthly' => ['price_usd' => 5, 'days' => 30],
        'yearly' => ['price_usd' => 40, 'days' => 365],
    ],

    'contact_email' => env('CONTACT_EMAIL', 'contact@yameen.app'),

    'font_styles' => ['kufi', 'naskh', 'sans', 'serif', 'display', 'handwriting'],

    'fonts' => [
        // Build output of scripts/fonts/build.py (fonts.json + files/).
        'path' => storage_path('app/fonts'),
        // Public URL of the font files. public/font-files is a symlink to {path}/files.
        // Not under /fonts, which is a page route.
        'public_url' => env('FONT_FILES_URL', '/font-files'),
        'python' => env('FONTS_PYTHON', 'python3'),
        'overrides' => resource_path('fonts/overrides.json'),
        'pairings' => resource_path('fonts/pairings.json'),
        'default_sample' => [
            'ar' => 'الخط العربي فن يبدأ من اليمين',
            'en' => 'الخط العربي فن يبدأ من اليمين',
        ],
    ],
];
