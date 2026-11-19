// Pro only: served by /pro/svg.js to Pro subscribers (see ProAssetController).
// Builds a standalone SVG made of paths: no font needed to open it.
import { frame } from '../export/frame';

export function toSvg(result, { color = '#1E1A2B', background = null, align = 'right', title = '' } = {}) {
    const f = frame(result, { align });
    const r = (n) => Math.round(n * 100) / 100;
    const paths = result.lines.flatMap((line, i) => line.glyphs.map((g) => {
        const o = f.origins[i];
        return `<path transform="matrix(1 0 0 -1 ${r(o.x + g.x)} ${r(o.y - g.y)})" d="${g.d}"/>`;
    }));
    const esc = (s) => s.replace(/[<>&"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' })[c]);
    return [
        `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${r(f.width)} ${r(f.height)}" width="${r(f.width / 10)}" height="${r(f.height / 10)}">`,
        title ? `<title>${esc(title)}</title>` : '',
        background ? `<rect width="100%" height="100%" fill="${background}"/>` : '',
        `<g fill="${color}">`,
        ...paths,
        '</g></svg>',
    ].join('');
}
