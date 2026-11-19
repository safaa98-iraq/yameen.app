// Pro only: served by /pro/batch.js to Pro subscribers (see ProAssetController).
// Processes SRT, TXT, CSV and JSON files in the browser, keeping their structure.
import Papa from 'papaparse';
import { fixText } from '../text/fixer';

export const MAX_BYTES = 5 * 1024 * 1024;

export function detectType(name) {
    const ext = name.toLowerCase().split('.').pop();
    return ['srt', 'txt', 'csv', 'json'].includes(ext) ? ext : null;
}

/** Columns (CSV) or string keys (JSON) the user can choose from. */
export function inspect(type, text) {
    if (type === 'csv') {
        const rows = Papa.parse(text, { skipEmptyLines: false }).data;
        return { fields: (rows[0] ?? []).map(String) };
    }
    if (type === 'json') {
        const keys = new Set();
        const walk = (v) => {
            if (Array.isArray(v)) v.forEach(walk);
            else if (v && typeof v === 'object') {
                for (const [k, val] of Object.entries(v)) {
                    if (typeof val === 'string') keys.add(k);
                    else walk(val);
                }
            }
        };
        walk(JSON.parse(text));
        return { fields: [...keys] };
    }
    return { fields: [] };
}

function fixSrt(text, opts) {
    const eol = text.includes('\r\n') ? '\r\n' : '\n';
    return text.split(/\r?\n\r?\n/).map((block) => {
        const lines = block.split(/\r?\n/);
        const t = lines.findIndex((l) => l.includes('-->'));
        if (t === -1) return block;
        return [...lines.slice(0, t + 1), ...lines.slice(t + 1).map((l) => fixText(l, opts))].join(eol);
    }).join(eol + eol);
}

/**
 * @param {'srt'|'txt'|'csv'|'json'} type
 * @param {string} text
 * @param {{ fields?: string[], options?: object }} config
 */
export function process(type, text, { fields = [], options = {} } = {}) {
    if (type === 'srt') return fixSrt(text, options);
    if (type === 'txt') return fixText(text, options);
    if (type === 'csv') {
        const parsed = Papa.parse(text, { skipEmptyLines: false });
        const [header = [], ...rows] = parsed.data;
        const idx = header.map((h, i) => (fields.includes(String(h)) ? i : -1)).filter((i) => i >= 0);
        const out = rows.map((row) => row.map((cell, i) => (idx.includes(i) && typeof cell === 'string' ? fixText(cell, options) : cell)));
        return Papa.unparse([header, ...out], { newline: parsed.meta.linebreak || '\n' });
    }
    if (type === 'json') {
        const walk = (v) => {
            if (Array.isArray(v)) return v.map(walk);
            if (v && typeof v === 'object') {
                return Object.fromEntries(Object.entries(v).map(([k, val]) => [k, typeof val === 'string' && fields.includes(k) ? fixText(val, options) : walk(val)]));
            }
            return v;
        };
        const indent = /^\s*[[{]\s*\n(\s+)/.exec(text)?.[1]?.length ?? 2;
        return JSON.stringify(walk(JSON.parse(text)), null, indent) + (text.endsWith('\n') ? '\n' : '');
    }
    throw new Error('unsupported type');
}
