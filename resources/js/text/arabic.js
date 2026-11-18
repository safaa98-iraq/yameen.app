// Shared Arabic character data.

// Harakat, Quranic annotation marks and superscript alef. Tatweel is not included.
export const DIACRITICS = /[\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06DC\u06DF-\u06E4\u06E7\u06E8\u06EA-\u06ED\u08D3-\u08E1\u08E3-\u08FF]/g;

export const TATWEEL = 'ـ';

const MARK_RE = /[\u0610-\u061A\u064B-\u065F\u0670\u06D6-\u06DC\u06DF-\u06E4\u06E7\u06E8\u06EA-\u06ED\u08D3-\u08E1\u08E3-\u08FF]/;

/** Combining mark: transparent for joining, kept together with its base when reordering. */
export function isMark(ch) {
    return MARK_RE.test(ch);
}

/** Any Arabic letter, in base or presentation form. */
export const ARABIC_CHAR = /[\u0600-ۿݐ-ݿࢠ-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;

export const PRESENTATION_FORM = /[\uFB50-\uFDFF\uFE70-\uFEFF]/;

export function hasArabic(text) {
    return ARABIC_CHAR.test(text);
}

export function stripDiacritics(text) {
    return text.replace(DIACRITICS, '');
}

/** Split keeping the line separators, so CRLF/LF are preserved exactly. */
export function mapLines(text, fn) {
    return text
        .split(/(\r\n|\n|\r)/)
        .map((part, i) => (i % 2 === 1 ? part : fn(part)))
        .join('');
}
