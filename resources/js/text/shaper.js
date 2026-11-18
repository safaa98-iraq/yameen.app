// Arabic contextual shaping to Presentation Forms (A/B), including lam-alef
// ligatures. Written in-house: the published npm reshapers have not been
// updated since 2022 and none handle marks and ZWJ/ZWNJ consistently.

import { isMark } from './arabic';

// [isolated, final, initial, medial]. Right-joining letters have only two forms.
export const FORMS = {
    'ء': ['ﺀ'], // ء (non-joining)
    'آ': ['ﺁ', 'ﺂ'], // آ
    'أ': ['ﺃ', 'ﺄ'], // أ
    'ؤ': ['ﺅ', 'ﺆ'], // ؤ
    'إ': ['ﺇ', 'ﺈ'], // إ
    'ئ': ['ﺉ', 'ﺊ', 'ﺋ', 'ﺌ'], // ئ
    'ا': ['ﺍ', 'ﺎ'], // ا
    'ب': ['ﺏ', 'ﺐ', 'ﺑ', 'ﺒ'], // ب
    'ة': ['ﺓ', 'ﺔ'], // ة
    'ت': ['ﺕ', 'ﺖ', 'ﺗ', 'ﺘ'], // ت
    'ث': ['ﺙ', 'ﺚ', 'ﺛ', 'ﺜ'], // ث
    'ج': ['ﺝ', 'ﺞ', 'ﺟ', 'ﺠ'], // ج
    'ح': ['ﺡ', 'ﺢ', 'ﺣ', 'ﺤ'], // ح
    'خ': ['ﺥ', 'ﺦ', 'ﺧ', 'ﺨ'], // خ
    'د': ['ﺩ', 'ﺪ'], // د
    'ذ': ['ﺫ', 'ﺬ'], // ذ
    'ر': ['ﺭ', 'ﺮ'], // ر
    'ز': ['ﺯ', 'ﺰ'], // ز
    'س': ['ﺱ', 'ﺲ', 'ﺳ', 'ﺴ'], // س
    'ش': ['ﺵ', 'ﺶ', 'ﺷ', 'ﺸ'], // ش
    'ص': ['ﺹ', 'ﺺ', 'ﺻ', 'ﺼ'], // ص
    'ض': ['ﺽ', 'ﺾ', 'ﺿ', 'ﻀ'], // ض
    'ط': ['ﻁ', 'ﻂ', 'ﻃ', 'ﻄ'], // ط
    'ظ': ['ﻅ', 'ﻆ', 'ﻇ', 'ﻈ'], // ظ
    'ع': ['ﻉ', 'ﻊ', 'ﻋ', 'ﻌ'], // ع
    'غ': ['ﻍ', 'ﻎ', 'ﻏ', 'ﻐ'], // غ
    'ف': ['ﻑ', 'ﻒ', 'ﻓ', 'ﻔ'], // ف
    'ق': ['ﻕ', 'ﻖ', 'ﻗ', 'ﻘ'], // ق
    'ك': ['ﻙ', 'ﻚ', 'ﻛ', 'ﻜ'], // ك
    'ل': ['ﻝ', 'ﻞ', 'ﻟ', 'ﻠ'], // ل
    'م': ['ﻡ', 'ﻢ', 'ﻣ', 'ﻤ'], // م
    'ن': ['ﻥ', 'ﻦ', 'ﻧ', 'ﻨ'], // ن
    'ه': ['ﻩ', 'ﻪ', 'ﻫ', 'ﻬ'], // ه
    'و': ['ﻭ', 'ﻮ'], // و
    'ى': ['ﻯ', 'ﻰ'], // ى
    'ي': ['ﻱ', 'ﻲ', 'ﻳ', 'ﻴ'], // ي
    // Persian / Kurdish / Urdu letters (Presentation Forms-A)
    'پ': ['ﭖ', 'ﭗ', 'ﭘ', 'ﭙ'], // پ
    'چ': ['ﭺ', 'ﭻ', 'ﭼ', 'ﭽ'], // چ
    'ژ': ['ﮊ', 'ﮋ'], // ژ
    'ڤ': ['ﭪ', 'ﭫ', 'ﭬ', 'ﭭ'], // ڤ
    'ک': ['ﮎ', 'ﮏ', 'ﮐ', 'ﮑ'], // ک
    'گ': ['ﮒ', 'ﮓ', 'ﮔ', 'ﮕ'], // گ
    'ی': ['ﯼ', 'ﯽ', 'ﯾ', 'ﯿ'], // ی
};

// Lam + alef variant → [isolated, final] ligature.
const LAM = 'ل';
export const LAM_ALEF = {
    'آ': ['ﻵ', 'ﻶ'],
    'أ': ['ﻷ', 'ﻸ'],
    'إ': ['ﻹ', 'ﻺ'],
    'ا': ['ﻻ', 'ﻼ'],
};

const ZWNJ = '‌';
const ZWJ = '‍';
const TATWEEL = 'ـ';

/** Can this character connect to the following letter? */
function joinsForward(ch) {
    if (ch === ZWJ || ch === TATWEEL) return true;
    const forms = FORMS[ch];
    return Boolean(forms && forms.length === 4);
}

/** Can this character connect to the preceding letter? */
function joinsBackward(ch) {
    if (ch === ZWJ || ch === TATWEEL) return true;
    const forms = FORMS[ch];
    return Boolean(forms && forms.length >= 2);
}

/**
 * Convert logical Arabic text into presentation forms. Text order is unchanged.
 * Marks stay after their base letter and do not break joining.
 * ZWJ/ZWNJ affect joining and are removed from the output.
 */
export function shape(text) {
    const chars = Array.from(text);
    const n = chars.length;

    // Index of the nearest non-mark neighbour in each direction.
    const prevBase = (i) => {
        for (let j = i - 1; j >= 0; j--) if (!isMark(chars[j])) return j;
        return -1;
    };
    const nextBase = (i) => {
        for (let j = i + 1; j < n; j++) if (!isMark(chars[j])) return j;
        return -1;
    };

    let out = '';
    for (let i = 0; i < n; i++) {
        const ch = chars[i];

        if (ch === ZWJ || ch === ZWNJ) continue;

        const forms = FORMS[ch];
        if (!forms) {
            out += ch;
            continue;
        }

        const p = prevBase(i);
        const joinPrev = p >= 0 && joinsForward(chars[p]) && joinsBackward(ch);

        // Lam-alef ligature (marks between lam and alef are kept after the ligature).
        if (ch === LAM) {
            const a = nextBase(i);
            if (a >= 0 && LAM_ALEF[chars[a]]) {
                out += LAM_ALEF[chars[a]][joinPrev ? 1 : 0];
                for (let j = i + 1; j < a; j++) out += chars[j];
                i = a;
                continue;
            }
        }

        const nx = nextBase(i);
        const joinNext = nx >= 0 && joinsForward(ch) && joinsBackward(chars[nx]);

        let form;
        if (forms.length === 1) form = forms[0];
        else if (forms.length === 2) form = forms[joinPrev ? 1 : 0];
        else if (joinPrev && joinNext) form = forms[3];
        else if (joinNext) form = forms[2];
        else if (joinPrev) form = forms[1];
        else form = forms[0];

        out += form;
    }
    return out;
}
