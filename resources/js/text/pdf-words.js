// Repair Arabic words whose letter pairs were swapped by the PDF producer.
//
// Some PDF exporters (notably Word with certain Arabic fonts) map ligature
// glyphs to their two letters in visual order, so copied text contains words
// like «يف» (في), «عىل» (على), «كربالء» (كربلاء) or «اجلماهريية» (الجماهيرية).
// We detect that a text has this problem, then fix each unknown word by
// trying swaps of adjacent letters and keeping the most frequent dictionary
// word. The dictionary is a frequency-ranked word list (rank = line number).

const LETTERS = /[ء-غف-ي]+/g;
const MARKS = /[ؐ-ًؚ-ٰٟ]/g;
const PREFIXES = ['وال', 'فال', 'بال', 'كال', 'لل', 'ال', 'و', 'ف', 'ب', 'ل', 'ك'];
const MAX_SWAPS = 3;

/** Build a lookup from the word list text (one word per line, most frequent first). */
export function buildDictionary(text) {
    const rank = new Map();
    let i = 0;
    for (const word of text.split('\n')) {
        if (word && !rank.has(word)) rank.set(word, i++);
    }
    return rank;
}

/** Rank of a word, allowing common one-letter and article prefixes. Lower is more frequent. */
function wordRank(dict, word) {
    if (dict.has(word)) return dict.get(word);
    let best = Infinity;
    for (const p of PREFIXES) {
        if (word.length >= p.length + 3 && word.startsWith(p)) {
            const rest = word.slice(p.length);
            const r = dict.get(rest) ?? dict.get('ال' + rest);
            if (r === undefined) continue;
            // The list omits prefixed forms, so the article costs almost nothing;
            // one-letter prefixes are less certain («لأربعين» vs «الأربعين»).
            best = Math.min(best, p === 'ال' ? r + 1 : r * 2 + 50);
        }
    }
    return best;
}

/** Signs that a text has swapped letter pairs (none of these occur in correct Arabic). */
export function swapSignals(text) {
    let n = 0;
    for (const w of text.replace(MARKS, '').match(LETTERS) ?? []) {
        if (w === 'يف' || w === 'إىل' || w === 'عىل' || w === 'ىف') n += 2;
        else if (/ى./.test(w)) n++; // alef maqsura is always final
        else if (/ة./.test(w)) n++; // taa marbuta is always final
        else if (/^ا[أإآ]/.test(w)) n++; // «األرض» for «الأرض»
        else if (/^ا[جحخ]ل/.test(w)) n++; // «اجلماهريية» for «الجماهيرية»
    }
    return n;
}

/**
 * Variants of a word with up to MAX_SWAPS non-overlapping adjacent letters swapped.
 * Yields { word, keys } where keys are the swapped pairs as they appear in the
 * input (e.g. "ني" for «بني» → «بين»).
 */
function* swapVariants(word) {
    const n = word.length;
    function* pick(start, left, chars, keys) {
        if (left === 0 || start >= n - 1) return;
        for (let i = start; i < n - 1; i++) {
            if (chars[i] === chars[i + 1]) continue;
            const next = chars.slice();
            [next[i], next[i + 1]] = [next[i + 1], next[i]];
            const nextKeys = [...keys, chars[i] + chars[i + 1]];
            yield { word: next.join(''), keys: nextKeys };
            yield* pick(i + 2, left - 1, next, nextKeys);
        }
    }
    yield* pick(0, MAX_SWAPS, Array.from(word), []);
}

// Each extra swap must be justified by a much more frequent word: one swap per
// corrupted ligature, so fewer swaps is more likely right («الطبيني» → «الطبيين»,
// not «الطيبين»). Swaps of pairs already seen swapped in the same text are cheap.
const SWAP_COST = 10;
const LIKELY_SWAP_COST = 3;
const CONFIRMED_SWAP_COST = 2;

// Letter pairs that Arabic fonts commonly draw as one ligature, in logical order.
// The PDF problem stores them reversed, so the keys we look for are these pairs
// reversed (e.g. «في» appears as «يف», «ين» as «ني», «شر» as «رش»).
const LIGATURES = [
    ['ل', 'اأإآجحخمىيه'],
    ['بتثنيئ', 'رزىينمجحخه'],
    ['م', 'اجحخمى'],
    ['جحخ', 'مى'],
    ['سشصض', 'رىي'],
    ['فق', 'ىي'],
];
const LIKELY = new Set();
for (const [firsts, seconds] of LIGATURES) {
    for (const a of firsts) for (const b of seconds) LIKELY.add(b + a);
}

// Short function words that this corruption turns into other (rare) words.
// Only applied when the text clearly has the problem.
const COMMON_SWAPS = {
    يف: 'في', هلا: 'لها', هبا: 'بها', هلم: 'لهم', هبم: 'بهم', هلن: 'لهن',
    هلذا: 'لهذا', هبذا: 'بهذا', هلذه: 'لهذه', هبذه: 'بهذه', فيام: 'فيما', بيام: 'بيما', كام: 'كما',
};

/**
 * Best correction for one word: { word, keys } or null to keep it.
 * Known words are only reconsidered when `confirmed` is given: then every
 * swapped pair must already be confirmed in the same text, and the result must
 * be much more frequent («بني» → «بين», but «طولية» stays).
 */
export function repairWord(dict, word, confirmed = null, { reconsiderKnown = false } = {}) {
    if (word.length < 2 || word.length > 16) return null;
    const original = wordRank(dict, word);
    const known = original !== Infinity;
    if (known && !reconsiderKnown) return null;

    const bases = [{ word, keys: [] }];
    // «ألربعني» for «الأربعين»: the article lost its alef and lam-hamza was swapped.
    if (/^[أإآ]ل/.test(word)) bases.push({ word: 'ال' + word[0] + word.slice(2), keys: ['ال'] });

    let best = null;
    let bestScore = Infinity;
    for (const base of bases) {
        const variants = base.keys.length ? [base] : [];
        for (const v of swapVariants(base.word)) variants.push({ word: v.word, keys: [...base.keys, ...v.keys] });
        for (const v of variants) {
            if (v.word === word) continue;
            const count = (k) => confirmed?.get(k) ?? 0;
            const seen = (k) => count(k) >= 2;
            // Known words: every swap must be confirmed in this text (once is enough for ligature pairs).
            if (known && !v.keys.every((k) => seen(k) || (LIKELY.has(k) && count(k) >= 1))) continue;
            // A correct word starting with the article keeps it («الفردي» never becomes «لافردي»).
            if (known && word.startsWith('ال') && !v.word.startsWith('ال')) continue;
            const r = wordRank(dict, v.word);
            if (r === Infinity) continue;
            const cost = (k) => (seen(k) ? CONFIRMED_SWAP_COST : LIKELY.has(k) ? LIKELY_SWAP_COST : SWAP_COST);
            const score = (r + 1) * v.keys.reduce((c, k) => c * cost(k), 1);
            if (score < bestScore) {
                best = v;
                bestScore = score;
            }
        }
    }
    // A known word is replaced only by a more frequent one, and only through pairs
    // this text is already known to swap (checked above).
    if (best && known && wordRank(dict, best.word) >= original) return null;
    return best;
}

const TOKEN = /[ء-غف-يؐ-ًؚ-ٰٟ]+/g;

/**
 * Repair a whole text. Returns { text, fixed } where fixed counts changed words.
 * Pass 1 fixes unknown words and records which letter pairs were swapped.
 * Pass 2 (only when the text clearly has the problem) reconsiders known words
 * using the pairs confirmed in pass 1.
 */
export function repairSwappedWords(text, dict, { aggressive = false } = {}) {
    const confirmed = new Map();
    const cache = new Map();
    const bare = (t) => t.replace(MARKS, '');

    const words = [...new Set((text.match(TOKEN) ?? []).map(bare))];

    // Pass 1: unknown words, plain costs; count which pairs were swapped.
    for (const w of words) {
        const r = repairWord(dict, w);
        cache.set(w, r);
        for (const k of r?.keys ?? []) confirmed.set(k, (confirmed.get(k) ?? 0) + 1);
    }
    // Pass 2: redo unknown words preferring the pairs this text tends to swap.
    for (const w of words) {
        if (cache.get(w)) cache.set(w, repairWord(dict, w, confirmed));
    }
    // Pass 3: known words, only when the text clearly has the problem.
    if (aggressive) {
        for (const w of words) {
            if (cache.get(w) || wordRank(dict, w) === Infinity) continue;
            if (COMMON_SWAPS[w]) cache.set(w, { word: COMMON_SWAPS[w], keys: [] });
            else cache.set(w, repairWord(dict, w, confirmed, { reconsiderKnown: true }));
        }
    }

    // Split into parts so the UI can highlight changed words.
    const parts = [];
    let fixed = 0;
    let last = 0;
    for (const m of text.matchAll(TOKEN)) {
        const r = cache.get(bare(m[0]));
        if (!r || r.word === bare(m[0])) continue;
        if (m.index > last) parts.push({ text: text.slice(last, m.index) });
        parts.push({ text: r.word, from: m[0] });
        last = m.index + m[0].length;
        fixed++;
    }
    if (last < text.length) parts.push({ text: text.slice(last) });
    return { text: parts.map((p) => p.text).join(''), fixed, parts };
}

/**
 * Brackets come out mirrored from the same PDFs: «)2017-2024(». When the first
 * bracket of the text is a closer, swap every bracket (pairs often span lines).
 */
export function fixMirroredBrackets(text) {
    const first = text.match(/[()[\]{}]/)?.[0];
    if (!first || !')]}'.includes(first)) return text;
    const swap = { '(': ')', ')': '(', '[': ']', ']': '[', '{': '}', '}': '{' };
    return text.replace(/[()[\]{}]/g, (c) => swap[c]);
}

/** Lines made only of marks and punctuation («ً», «ً.») are layout debris from the PDF. */
function isDebris(line) {
    return line.trim() !== '' && !/[\p{L}\p{N}]/u.test(line.replace(MARKS, ''));
}

/**
 * Line-level cleanup for text with swapped pairs:
 * - mirrored brackets,
 * - lines that only hold stray diacritics or punctuation,
 * - a first letter that the PDF moved to the end of the line («ؤدي … ت» → «تؤدي …»).
 */
export function repairLines(text, dict) {
    const lines = fixMirroredBrackets(text).split(/\r?\n/);
    const out = [];
    for (let line of lines) {
        if (isDebris(line)) continue;
        // Stray diacritics standing alone between spaces («0.0005) ً، مما»).
        line = line.replace(/(^|\s)[\u064B-\u0652]+(?=\s|[،.؛:]|$)/g, '$1');
        const m = line.match(/^(\S+)(.*)\s([ء-ي])\s*$/);
        if (m) {
            const [, first, middle, letter] = m;
            const joined = letter + first;
            if (wordRank(dict, joined) < wordRank(dict, first)) line = joined + middle;
        }
        out.push(line);
    }
    return out.join('\n');
}

/** Number of signals needed before touching words: fewer for short texts. */
export function hasSwappedPairs(text) {
    const words = (text.match(LETTERS) ?? []).length;
    return swapSignals(text) >= (words < 30 ? 1 : 2);
}
