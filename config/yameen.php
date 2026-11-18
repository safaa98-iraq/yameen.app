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
];
