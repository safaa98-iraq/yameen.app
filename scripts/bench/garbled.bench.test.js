// Measures how well swapped-pair repair restores real Arabic text.
// CORPUS: a directory of Arabic .txt/.md files (not committed). Without it,
// the site's own Arabic content is used.
import { it } from 'vitest';
import { readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { buildDictionary, repairLines, repairSwappedWords, swapSignals } from '../../resources/js/text/pdf-words';

const dict = buildDictionary(readFileSync('resources/js/data/ar-words.txt', 'utf8'));

function files(dir) {
    return readdirSync(dir).flatMap((f) => {
        const p = join(dir, f);
        return statSync(p).isDirectory() ? files(p) : /\.(txt|md)$/.test(f) ? [p] : [];
    });
}

function corpus() {
    const dir = process.env.CORPUS ?? 'resources/content/ar';
    const docs = files(dir).map((f) => readFileSync(f, 'utf8')
        .replace(/<[^>]+>/g, ' ')          // corpus markup
        .replace(/^---[\s\S]*?---/, ' ')   // front matter
        .replace(/[#*`>\-|]/g, ' '));
    // Paragraph-sized chunks, as a user would paste them.
    return docs.flatMap((d) => d.split(/\n\s*\n/)).map((p) => p.replace(/\s+/g, ' ').trim())
        .filter((p) => (p.match(/[ء-ي]+/g) ?? []).length >= 25).slice(0, Number(process.env.LIMIT ?? 400));
}

// Ligature pairs (logical order) observed swapped in real Word-exported PDFs.
const LAM = ['لا', 'لأ', 'لإ', 'لآ', 'لم', 'لى', 'لخ', 'لح', 'لج', 'له'];
const OTHER = ['ين', 'ير', 'في', 'ما', 'لي', 'تح', 'ضى', 'مح', 'بر', 'تج', 'ني', 'به', 'شر', 'صى', 'جم'];

// Deterministic pseudo-random so runs are comparable.
let seed = 42;
const rand = () => ((seed = (seed * 1103515245 + 12345) % 2147483648) / 2147483648);

function corruptWord(w, pOther) {
    let out = '';
    for (let i = 0; i < w.length; ) {
        const pair = w.slice(i, i + 2);
        const p = LAM.includes(pair) ? 1 : OTHER.includes(pair) ? pOther : 0;
        if (p && rand() < p) {
            out += pair[1] + pair[0];
            i += 2;
        } else {
            out += w[i++];
        }
    }
    return out;
}

const corrupt = (text, pOther) => text.replace(/[ء-ي]+/g, (w) => corruptWord(w, pOther));
const words = (t) => t.match(/[ء-ي]+/g) ?? [];

function run(name, pOther, paras) {
    seed = 42;
    let corrupted = 0, restored = 0, clean = 0, broken = 0, total = 0, ok = 0;
    const misses = new Map();
    for (const para of paras) {
        const bad = corrupt(para, pOther);
        const fixed = repairSwappedWords(repairLines(bad, dict), dict, { aggressive: swapSignals(bad) >= 1 }).text;
        const [w0, w1, w2] = [words(para), words(bad), words(fixed)];
        if (w0.length !== w2.length) continue;
        w0.forEach((orig, i) => {
            total++;
            if (w2[i] === orig) ok++;
            if (w1[i] !== orig) {
                corrupted++;
                if (w2[i] === orig) restored++;
                else misses.set(`${w1[i]} → ${w2[i]} (≠ ${orig})`, (misses.get(`${w1[i]} → ${w2[i]} (≠ ${orig})`) ?? 0) + 1);
            } else {
                clean++;
                if (w2[i] !== orig) {
                    broken++;
                    misses.set(`[ok] ${orig} → ${w2[i]}`, (misses.get(`[ok] ${orig} → ${w2[i]}`) ?? 0) + 1);
                }
            }
        });
    }
    const pct = (a, b) => (100 * a / Math.max(1, b)).toFixed(2) + '%';
    const top = [...misses].sort((a, b) => b[1] - a[1]).slice(0, 25).map(([k, v]) => `   ${v}× ${k}`).join('\n');
    return `${name}: words ${total}, corrupted ${corrupted}\n  restored ${pct(restored, corrupted)} | correct words broken ${pct(broken, clean)} | word accuracy ${pct(ok, total)}\n${top}`;
}

it('benchmark', () => {
    const paras = corpus();
    const report = [run('full (all ligature pairs swapped)', 1, paras), run('mixed (lam pairs always, others 60%)', 0.6, paras)].join('\n\n');
    writeFileSync(process.env.OUT ?? '/dev/stdout', report + '\n');
});
