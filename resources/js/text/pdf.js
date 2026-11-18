// Repair Arabic text copied from PDFs: presentation forms back to base
// letters, and (optionally) visual order back to logical order.
import { ARABIC_CHAR, mapLines, PRESENTATION_FORM } from './arabic';
import { FORMS, LAM_ALEF } from './shaper';
import { toVisual } from './bidi';

const ISOLATED = 0;
const FINAL = 1;
const INITIAL = 2;
const MEDIAL = 3;

// Presentation form → position (0 isolated, 1 final, 2 initial, 3 medial).
const FORM_POSITION = new Map();
for (const forms of Object.values(FORMS)) forms.forEach((f, i) => FORM_POSITION.set(f, i));
for (const [iso, fin] of Object.values(LAM_ALEF)) {
    FORM_POSITION.set(iso, ISOLATED);
    FORM_POSITION.set(fin, FINAL);
}

/** NFKC applied to the Arabic presentation blocks only; everything else is untouched. */
export function normalizePresentationForms(text) {
    return text.replace(/[\uFB50-\uFDFF\uFE70-\uFEFF]/g, (ch) => {
        if (ch === '\uFEFF') return ''; // byte order mark / zero-width no-break space
        // Isolated harakat (FE70–FE7F) decompose to "space + mark": keep the mark only.
        return ch.normalize('NFKC').replace(/ /g, '');
    });
}

const WORD = new RegExp(`${ARABIC_CHAR.source}+`, 'g');
const LETTERS_ONLY = /[\u064B-\u065F\u0670ـ]/g;

/**
 * Guess whether Arabic text is in reversed (visual) order.
 * Returns { reversed: true | false | null, logical, visual } where null means unsure.
 */
export function detectReversed(text) {
    let logical = 0;
    let visual = 0;

    for (const raw of text.match(WORD) ?? []) {
        const chars = Array.from(raw);
        if (chars.length < 2) continue;

        // Strongest signal: the shape of the first and last presentation forms.
        const first = FORM_POSITION.get(chars[0]);
        const last = FORM_POSITION.get(chars[chars.length - 1]);
        if (first === INITIAL || last === FINAL) logical += 2;
        if (first === FINAL || last === INITIAL) visual += 2;
        if (first === MEDIAL || last === MEDIAL) visual += 0; // ambiguous, ignore

        // Letter patterns on base letters.
        const word = normalizePresentationForms(raw).replace(LETTERS_ONLY, '');
        if (word.length < 3) continue;
        if (word.startsWith('ال')) logical++;
        if (word.endsWith('لا') && !word.startsWith('ال')) visual++;
        if (/[ةى]$/.test(word)) logical++;
        if (/^[ةى]/.test(word)) visual += 2; // these letters never start a word
    }

    const total = logical + visual;
    let reversed = null;
    if (total >= 2) {
        if (visual >= 2 * logical && visual >= 2) reversed = true;
        else if (logical >= 2 * visual && logical >= 2) reversed = false;
    }
    return { reversed, logical, visual };
}

/** Reverse each line from visual to logical order, keeping Latin and numbers intact. */
export function reverseLines(text) {
    return mapLines(text, (line) => toVisual(line, /[\u0600-ۿ\uFB50-\uFEFF]/.test(line) ? 'rtl' : 'ltr'));
}

/**
 * Full PDF repair. `reverse`: 'auto' (use detection when confident), true or false.
 * Reversal runs before normalization so lam-alef ligatures decompose in logical order.
 */
export function fixPdfText(text, { reverse = 'auto' } = {}) {
    if (!text) return { text: '', detection: { reversed: null, logical: 0, visual: 0 }, reversedApplied: false };
    const detection = detectReversed(text);
    const apply = reverse === 'auto' ? detection.reversed === true : Boolean(reverse);
    const ordered = apply ? reverseLines(text) : text;
    return { text: normalizePresentationForms(ordered), detection, reversedApplied: apply };
}

export function hasPresentationForms(text) {
    return PRESENTATION_FORM.test(text);
}
