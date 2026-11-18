<?php

return [
    'brand' => 'Yameen',
    'tagline' => 'Type Arabic, pick a font, paste it anywhere.',
    'skip_to_content' => 'Skip to content',

    'nav' => [
        'label' => 'Main navigation',
        'fix' => 'Fixer',
        'fonts' => 'Fonts',
        'export' => 'Export',
        'pricing' => 'Pricing',
        'login' => 'Log in',
        'menu' => 'Menu',
        'close' => 'Close',
    ],

    'credits' => [
        'counter' => 'Credits today: :remaining of :limit',
        'unlimited' => 'Credits: unlimited',
    ],

    'lang_switch' => 'العربية',
    'lang_switch_label' => 'اعرض هذه الصفحة بالعربية',

    'tool' => [
        'input_label' => 'Text',
        'placeholder' => 'Type your Arabic text here',
        'options_label' => 'Fix options',
        'opt_strip_tashkeel' => 'Remove diacritics',
        'opt_shape' => 'Connect letters',
        'opt_reverse' => 'Reverse direction',
        'font_label' => 'Font',
        'result_label' => 'Result',
        'copy_for_app' => 'Copy for your app',
        'copied' => 'Copied',
        'copy_plain' => 'Copy original text',
        'export_image' => 'Export image',
        'shortcut_hint' => 'Ctrl + Enter to run',
        'empty' => 'Type some text to see the result here.',
    ],

    'home' => [
        'title' => 'Yameen: fix Arabic text for apps that don’t support it',
        'description' => 'Type Arabic, preview it in free Arabic fonts, then copy a version that displays correctly in software without Arabic support, or export it as an image.',
        'h1' => 'Type Arabic, pick a font, paste it anywhere',
        'sample_text' => 'الخط العربي فن يبدأ من اليمين',
        'specimen_heading' => 'Font preview',
        'all_fonts' => 'All fonts',
        's1_title' => 'Why do Arabic letters show up disconnected?',
        's1_body' => 'Many design, video and game tools don’t apply Arabic letter joining, so every letter appears on its own and in reverse order. Yameen converts the text to connected letter forms and orders it visually, so it appears in your app the way you typed it.',
        's2_title' => 'Steps',
        's2_steps' => ['Type or paste your text.', 'Pick the font you will use in your app.', 'Copy the result and paste it into your app.'],
        's3_title' => 'FAQ',
    ],

    'fixer' => [
        'result_hint' => 'As it will appear in your app',
        'paste_title' => 'How to paste in :app',
        'font_note' => 'Use a font that includes connected Arabic letter forms in your app, or you will see boxes instead of letters.',
    ],

    'pdf' => [
        'input_label' => 'Text copied from PDF',
        'placeholder' => 'Paste the text here',
        'copy' => 'Copy text',
        'reverse' => 'Reverse order',
        'undo_reverse' => 'Undo reverse',
        'status_auto' => 'The order was reversed, so it was fixed automatically.',
        'status_unsure' => 'The text direction is unclear. Reverse the order if it looks backwards.',
        'status_logical' => 'The order is correct; letters were restored to their base forms.',
        'status_manual' => 'Order reversed manually.',
        'status_kept' => 'Order kept as is.',
    ],

    'cleaner' => [
        'options_label' => 'Cleaning options',
        'tashkeel' => 'Remove diacritics',
        'tatweel' => 'Remove tatweel',
        'alef' => 'Unify alef',
        'ya' => 'Unify ya',
        'spaces' => 'Collapse spaces',
        'trim' => 'Trim lines',
        'digits_label' => 'Digits',
        'digits_none' => 'Unchanged',
        'digits_western' => 'Western 123',
        'digits_arabic' => 'Eastern ١٢٣',
        'copy' => 'Copy text',
    ],

    'content' => [
        'faq' => 'FAQ',
        'related' => 'Related tools',
        'software_pages' => 'Fixer for other apps',
        'fix_for' => 'Fixer for :app',
    ],

    'placeholder' => [
        'title' => 'This page is being prepared',
        'body' => 'This tool is coming soon. You can use the fixer on the home page.',
        'back' => 'Back to the home page',
    ],

    'pages' => [
        'fix' => 'Arabic text fixer',
        'pdf-fix' => 'Fix Arabic text copied from PDF',
        'clean' => 'Text cleaner',
        'fonts' => 'Arabic fonts',
        'fonts-compare' => 'Compare fonts',
        'fonts-pairing' => 'Font pairing',
        'export' => 'Export image',
        'pricing' => 'Pricing',
        'login' => 'Log in',
        'about' => 'About',
        'privacy' => 'Privacy policy',
        'terms' => 'Terms of use',
        'contact' => 'Contact',
    ],

    'footer' => [
        'tools' => 'Tools',
        'fonts' => 'Fonts',
        'site' => 'Site',
        'fonts_credit' => 'Fonts shown are licensed under the SIL Open Font License and belong to their designers.',
        'ui_fonts' => 'Interface fonts: IBM Plex Sans Arabic, IBM Plex Sans and Reem Kufi.',
        'copyright' => '© :year Yameen',
    ],
];
