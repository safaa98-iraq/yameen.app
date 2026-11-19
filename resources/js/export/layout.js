// Text layout for export: HarfBuzz (WebAssembly) shapes each bidi run, and
// glyph outlines come from the font itself. Output is in font units and has
// no font dependency. Loaded only when the user exports.
import * as hb from 'harfbuzzjs';
import bidiFactory from 'bidi-js';
import { frame } from './frame';

export { frame };

const bidi = bidiFactory();
const fonts = new Map();

/** Load a TTF once and keep the HarfBuzz font. */
export async function loadFont(url) {
    if (!fonts.has(url)) {
        fonts.set(url, (async () => {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`font ${res.status}`);
            const face = new hb.Face(new hb.Blob(await res.arrayBuffer()), 0);
            return { font: new hb.Font(face), upem: face.upem };
        })());
    }
    return fonts.get(url);
}

const ARABIC = /[؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/;

/** Split a line into runs of equal bidi level (logical order). */
function runs(line) {
    const dir = ARABIC.test(line) ? 'rtl' : 'ltr';
    const { levels } = bidi.getEmbeddingLevels(line, dir);
    const out = [];
    for (let i = 0; i < line.length; ) {
        let j = i + 1;
        while (j < line.length && levels[j] === levels[i]) j++;
        out.push({ text: line.slice(i, j), level: levels[i] });
        i = j;
    }
    return out;
}

/** Unicode bidi rule L2 applied to runs: reverse sequences from the highest level down. */
function visualOrder(list) {
    const items = [...list];
    const max = Math.max(0, ...items.map((r) => r.level));
    const minOdd = Math.min(...items.map((r) => r.level).filter((l) => l % 2), Infinity);
    for (let level = max; level >= minOdd && level > 0; level--) {
        for (let i = 0; i < items.length; ) {
            if (items[i].level < level) {
                i++;
                continue;
            }
            let j = i;
            while (j < items.length && items[j].level >= level) j++;
            items.splice(i, j - i, ...items.slice(i, j).reverse());
            i = j;
        }
    }
    return items;
}

/**
 * Lay out text (one or more lines).
 * @returns {{ upem, ascender, descender, lineHeight, width, lines: {width, glyphs: {d, x, y}[]}[] }}
 */
export function layout(text, { font, upem }, { lineHeight = 1.6 } = {}) {
    const ext = font.hExtents();
    const lines = text.split(/\r?\n/).map((line) => {
        const glyphs = [];
        let pen = 0;
        for (const run of visualOrder(runs(line))) {
            const buf = new hb.Buffer();
            buf.addText(run.text);
            buf.guessSegmentProperties();
            buf.setDirection(run.level % 2 ? hb.Direction.RTL : hb.Direction.LTR);
            hb.shape(font, buf);
            const infos = buf.getGlyphInfos();
            const pos = buf.getGlyphPositions();
            infos.forEach((info, k) => {
                const d = font.glyphToPath(info.codepoint);
                if (d) glyphs.push({ d, x: pen + pos[k].xOffset, y: pos[k].yOffset });
                pen += pos[k].xAdvance;
            });
        }
        return { width: pen, glyphs };
    });
    return {
        upem,
        ascender: ext.ascender,
        descender: ext.descender,
        lineHeight: upem * lineHeight,
        width: Math.max(1, ...lines.map((l) => l.width)),
        lines,
    };
}

/** Draw onto a canvas at a pixel width; returns the canvas. */
export function drawToCanvas(result, { widthPx, color = '#1E1A2B', background = '#FFFFFF', align = 'right' }) {
    const f = frame(result, { align });
    const scale = widthPx / f.width;
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(f.width * scale);
    canvas.height = Math.round(f.height * scale);
    const ctx = canvas.getContext('2d');
    if (background) {
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    ctx.fillStyle = color;
    result.lines.forEach((line, i) => {
        const o = f.origins[i];
        for (const g of line.glyphs) {
            ctx.setTransform(scale, 0, 0, -scale, (o.x + g.x) * scale, (o.y - g.y) * scale);
            ctx.fill(new Path2D(g.d));
        }
    });
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    return canvas;
}
