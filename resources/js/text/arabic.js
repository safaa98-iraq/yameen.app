// Shared Arabic character data.

// Harakat, Quranic annotation marks and superscript alef. Tatweel is not included.
export const DIACRITICS = /[ؐ-ًؚ-ٰٟۖ-ۜ۟-۪ۤۧۨ-ۭ࣓-ࣣ࣡-ࣿ]/g;

export const TATWEEL = 'ـ';

const MARK_RE = /[ؐ-ًؚ-ٰٟۖ-ۜ۟-۪ۤۧۨ-ۭ࣓-ࣣ࣡-ࣿ]/;

/** Combining mark: transparent for joining, kept together with its base when reordering. */
export function isMark(ch) {
    return MARK_RE.test(ch);
}

/** Any Arabic letter, in base or presentation form. */
export const ARABIC_CHAR = /[؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/;

export const PRESENTATION_FORM = /[ﭐ-﷿ﹰ-﻿]/;

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
