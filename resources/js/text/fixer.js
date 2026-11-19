// Fixer pipeline: logical Arabic → text that displays correctly in software
// without Arabic shaping or bidi support (Photoshop without the Middle East
// composer, After Effects, game engines, 3D apps).
import { mapLines, stripDiacritics } from './arabic';
import { shape } from './shaper';
import { toVisual } from './bidi';
import { detectReversed, hasPresentationForms, normalizePresentationForms, reverseLines } from './pdf';

export { hasPresentationForms };

export const DEFAULT_OPTIONS = Object.freeze({
    stripTashkeel: true, // presentation forms do not position diacritics reliably
    shape: true,
    reverse: true,
    // Input that is already fixed, or copied from a PDF or an app, contains
    // presentation forms: restore it to logical Arabic before fixing.
    normalizeInput: true,
});

/** Logical Arabic from text that may already be shaped and/or in visual order. */
export function toLogical(text) {
    if (!hasPresentationForms(text)) return text;
    // Presentation forms in the fixer's input almost always come from a fixed or
    // copied text, so reverse unless the order is clearly logical already.
    const ordered = detectReversed(text).reversed === false ? text : reverseLines(text);
    return normalizePresentationForms(ordered);
}

/** Fix text line by line. Empty lines and line separators are preserved. */
export function fixText(text, options = {}) {
    const opts = { ...DEFAULT_OPTIONS, ...options };
    if (!text) return '';
    const input = opts.normalizeInput ? toLogical(text) : text;

    return mapLines(input, (line) => {
        let out = line;
        if (opts.stripTashkeel) out = stripDiacritics(out);
        if (opts.shape) out = shape(out);
        if (opts.reverse) out = toVisual(out);
        return out;
    });
}
