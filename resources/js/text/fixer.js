// Fixer pipeline: logical Arabic → text that displays correctly in software
// without Arabic shaping or bidi support (Photoshop without the Middle East
// composer, After Effects, game engines, 3D apps).
import { mapLines, stripDiacritics } from './arabic';
import { shape } from './shaper';
import { toVisual } from './bidi';

export const DEFAULT_OPTIONS = Object.freeze({
    stripTashkeel: true, // presentation forms do not position diacritics reliably
    shape: true,
    reverse: true,
});

/** Fix text line by line. Empty lines and line separators are preserved. */
export function fixText(text, options = {}) {
    const opts = { ...DEFAULT_OPTIONS, ...options };
    if (!text) return '';

    return mapLines(text, (line) => {
        let out = line;
        if (opts.stripTashkeel) out = stripDiacritics(out);
        if (opts.shape) out = shape(out);
        if (opts.reverse) out = toVisual(out);
        return out;
    });
}
