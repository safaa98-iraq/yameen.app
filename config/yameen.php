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
