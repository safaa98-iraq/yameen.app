// Visual reordering (Unicode bidi, UAX #9) for apps without bidi support.
import bidiFactory from 'bidi-js';
import { hasArabic, isMark } from './arabic';

let bidi;
const engine = () => (bidi ??= bidiFactory());

// Explicit directional formatting characters: not needed once text is in visual order.
const BIDI_CONTROLS = /[\u200E\u200F\u061C\u202A-\u202E\u2066-\u2069]/g;

/**
 * Reorder one line from logical to visual (left-to-right) order.
 * Lines containing Arabic use a right-to-left paragraph direction, so Latin
 * words and numbers keep their internal left-to-right order. Brackets inside
 * right-to-left runs are mirrored. Combining marks stay after their base.
 */
export function toVisual(line, direction = null) {
    if (!line) return line;
    const dir = direction ?? (hasArabic(line) ? 'rtl' : 'ltr');
    const b = engine();
    const levels = b.getEmbeddingLevels(line, dir);
    const mirrored = b.getMirroredCharactersMap(line, levels.levels);
    const order = b.getReorderedIndices(line, levels);

    // Cluster id per code unit: a mark belongs to the cluster of the base before it.
    const cluster = new Array(line.length);
    let id = -1;
    for (let i = 0; i < line.length; i++) {
        if (!(isMark(line[i]) && id >= 0)) id = i;
        cluster[i] = id;
    }

    // Reversal turns "base mark" into "mark base"; restore logical order inside each cluster.
    let out = '';
    for (let k = 0; k < order.length; ) {
        const c = cluster[order[k]];
        const group = [];
        while (k < order.length && cluster[order[k]] === c) group.push(order[k++]);
        group.sort((a, z) => a - z);
        for (const i of group) out += mirrored.get(i) ?? line[i];
    }

    return out.replace(BIDI_CONTROLS, '');
}
