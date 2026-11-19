// Benchmark: words stored fully reversed («رشنلل» for «للنشر»), mixed into
// correct text. Run: npx vitest run -c vitest.bench.config.js
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';
import { test } from 'vitest';
import { buildDictionary, repairSwappedWords } from '../../resources/js/text/pdf-words.js';

const dict = buildDictionary(readFileSync('resources/js/data/ar-words.txt', 'utf8'));
const files = (dir) => readdirSync(dir).flatMap((f) => {
    const p = join(dir, f);
    return statSync(p).isDirectory() ? files(p) : /\.md$/.test(f) ? [p] : [];
});

test('reversed words', () => {
    const words = files('resources/content/ar').flatMap((f) => readFileSync(f, 'utf8').replace(/^---[\s\S]*?---/, ' ').match(/[ء-ي]{4,}/g) ?? []);
    let seed = 7;
    const rand = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
    let reversed = 0, restored = 0, clean = 0, broken = 0;
    const misses = new Map();
    for (let i = 0; i < words.length; i += 40) {
        const chunk = words.slice(i, i + 40);
        const input = chunk.map((w) => (rand() < 0.15 ? [...w].reverse().join('') : w));
        const out = repairSwappedWords(input.join(' '), dict).text.split(' ');
        chunk.forEach((w, j) => {
            if (input[j] !== w) { reversed++; if (out[j] === w) restored++; else misses.set(`${input[j]} → ${out[j]} (≠ ${w})`, 1 + (misses.get(`${input[j]} → ${out[j]} (≠ ${w})`) ?? 0)); }
            else { clean++; if (out[j] !== w) broken++; }
        });
    }
    console.log(`reversed: ${reversed} | restored ${(100 * restored / reversed).toFixed(2)}% | correct words broken ${(100 * broken / clean).toFixed(2)}%`);
    console.log([...misses].sort((a, b) => b[1] - a[1]).slice(0, 15).map(([k, n]) => `  ${n}× ${k}`).join('\n'));
});
