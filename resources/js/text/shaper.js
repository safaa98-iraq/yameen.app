// Arabic contextual shaping to Presentation Forms (A/B), including lam-alef
// ligatures. Written in-house: the published npm reshapers have not been
// updated since 2022 and none handle marks and ZWJ/ZWNJ consistently.

import { isMark } from './arabic';

// [isolated, final, initial, medial]. Right-joining letters have only two forms.
export const FORMS = {
    '\u0621': ['\uFE80'], // ء (non-joining)
    '\u0622': ['\uFE81', '\uFE82'], // آ
    '\u0623': ['\uFE83', '\uFE84'], // أ
    '\u0624': ['\uFE85', '\uFE86'], // ؤ
    '\u0625': ['\uFE87', '\uFE88'], // إ
    '\u0626': ['\uFE89', '\uFE8A', '\uFE8B', '\uFE8C'], // ئ
    '\u0627': ['\uFE8D', '\uFE8E'], // ا
    '\u0628': ['\uFE8F', '\uFE90', '\uFE91', '\uFE92'], // ب
    '\u0629': ['\uFE93', '\uFE94'], // ة
    '\u062A': ['\uFE95', '\uFE96', '\uFE97', '\uFE98'], // ت
    '\u062B': ['\uFE99', '\uFE9A', '\uFE9B', '\uFE9C'], // ث
    '\u062C': ['\uFE9D', '\uFE9E', '\uFE9F', '\uFEA0'], // ج
    '\u062D': ['\uFEA1', '\uFEA2', '\uFEA3', '\uFEA4'], // ح
    '\u062E': ['\uFEA5', '\uFEA6', '\uFEA7', '\uFEA8'], // خ
    '\u062F': ['\uFEA9', '\uFEAA'], // د
    '\u0630': ['\uFEAB', '\uFEAC'], // ذ
    '\u0631': ['\uFEAD', '\uFEAE'], // ر
    '\u0632': ['\uFEAF', '\uFEB0'], // ز
    '\u0633': ['\uFEB1', '\uFEB2', '\uFEB3', '\uFEB4'], // س
    '\u0634': ['\uFEB5', '\uFEB6', '\uFEB7', '\uFEB8'], // ش
    '\u0635': ['\uFEB9', '\uFEBA', '\uFEBB', '\uFEBC'], // ص
    '\u0636': ['\uFEBD', '\uFEBE', '\uFEBF', '\uFEC0'], // ض
    '\u0637': ['\uFEC1', '\uFEC2', '\uFEC3', '\uFEC4'], // ط
    '\u0638': ['\uFEC5', '\uFEC6', '\uFEC7', '\uFEC8'], // ظ
    '\u0639': ['\uFEC9', '\uFECA', '\uFECB', '\uFECC'], // ع
    '\u063A': ['\uFECD', '\uFECE', '\uFECF', '\uFED0'], // غ
    '\u0641': ['\uFED1', '\uFED2', '\uFED3', '\uFED4'], // ف
    '\u0642': ['\uFED5', '\uFED6', '\uFED7', '\uFED8'], // ق
    '\u0643': ['\uFED9', '\uFEDA', '\uFEDB', '\uFEDC'], // ك
    '\u0644': ['\uFEDD', '\uFEDE', '\uFEDF', '\uFEE0'], // ل
    '\u0645': ['\uFEE1', '\uFEE2', '\uFEE3', '\uFEE4'], // م
    '\u0646': ['\uFEE5', '\uFEE6', '\uFEE7', '\uFEE8'], // ن
    '\u0647': ['\uFEE9', '\uFEEA', '\uFEEB', '\uFEEC'], // ه
    '\u0648': ['\uFEED', '\uFEEE'], // و
    '\u0649': ['\uFEEF', '\uFEF0'], // ى
    '\u064A': ['\uFEF1', '\uFEF2', '\uFEF3', '\uFEF4'], // ي
    // Persian / Kurdish / Urdu letters (Presentation Forms-A)
    '\u067E': ['\uFB56', '\uFB57', '\uFB58', '\uFB59'], // پ
    '\u0686': ['\uFB7A', '\uFB7B', '\uFB7C', '\uFB7D'], // چ
    '\u0698': ['\uFB8A', '\uFB8B'], // ژ
    '\u06A4': ['\uFB6A', '\uFB6B', '\uFB6C', '\uFB6D'], // ڤ
    '\u06A9': ['\uFB8E', '\uFB8F', '\uFB90', '\uFB91'], // ک
    '\u06AF': ['\uFB92', '\uFB93', '\uFB94', '\uFB95'], // گ
    '\u06CC': ['\uFBFC', '\uFBFD', '\uFBFE', '\uFBFF'], // ی
};

// Lam + alef variant → [isolated, final] ligature.
const LAM = '\u0644';
export const LAM_ALEF = {
    '\u0622': ['\uFEF5', '\uFEF6'],
    '\u0623': ['\uFEF7', '\uFEF8'],
    '\u0625': ['\uFEF9', '\uFEFA'],
    '\u0627': ['\uFEFB', '\uFEFC'],
};

const ZWNJ = '\u200C';
const ZWJ = '\u200D';
const TATWEEL = '\u0640';

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
 * Convert logical Arabic text into presentation forms (isolated letters are kept as base letters). Text order is unchanged.
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
        if (forms.length === 1) form = 0;
        else if (forms.length === 2) form = joinPrev ? 1 : 0;
        else if (joinPrev && joinNext) form = 3;
        else if (joinNext) form = 2;
        else if (joinPrev) form = 1;
        else form = 0;

        // Isolated letters stay as base letters: apps without shaping already draw
        // them in isolated form, and many fonts (Cairo, Tajawal, Almarai...) only
        // include initial, medial and final presentation forms.
        out += form === 0 ? ch : forms[form];
    }
    return out;
}
