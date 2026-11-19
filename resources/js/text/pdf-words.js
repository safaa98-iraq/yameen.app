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
const RARE_RANK = 150000;

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
function wordRank(dict, word, { oneLetter = true } = {}) {
    // A word may be listed itself as a rare corrupted entry («ليركز») and still
    // be a common word with a prefix («ل» + «يركز»): take the best reading.
    let best = dict.get(word) ?? hamzaRank(dict, word);
    for (const p of PREFIXES) {
        if (!oneLetter && p.length === 1) continue;
        if (word.length >= p.length + 3 && word.startsWith(p)) {
            const rest = word.slice(p.length);
            let r = dict.get(rest) ?? dict.get('ال' + rest) ?? hamzaRank(dict, rest);
            if (r === undefined) continue;
            // «لابحاث»: «ل» before a bare alef is almost never right.
            if (p === 'ل' && rest[0] === 'ا' && !rest.startsWith('ال')) r = r * 20 + 500;
            // The list omits prefixed forms, so the article costs almost nothing;
            // one-letter prefixes are less certain («لأربعين» vs «الأربعين»).
            best = Math.min(best ?? Infinity, p === 'ال' ? r + 1 : r * 2 + 50);
        }
    }
    return best ?? Infinity;
}

/** «اخلاقية» written without its hamza: rank of «أخلاقية», slightly penalised. */
function hamzaRank(dict, word) {
    if (word[0] !== 'ا' || word[1] === 'ل') return undefined;
    const r = dict.get('أ' + word.slice(1)) ?? dict.get('إ' + word.slice(1));
    return r === undefined ? undefined : r * 3 + 100;
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

function isRareOrUnknown(dict, word) {
    const r = wordRank(dict, word);
    return r === Infinity || r > RARE_RANK;
}

/**
 * Variants of a word with up to MAX_SWAPS non-overlapping adjacent letters swapped.
 * Yields { word, keys } where keys are the swapped pairs as they appear in the
 * input (e.g. "ني" for «بني» → «بين»).
 */
function* swapVariants(word, allowed = (key) => LIKELY.has(key)) {
    const n = word.length;
    function* pick(start, left, chars, keys) {
        if (left === 0 || start >= n - 1) return;
        for (let i = start; i < n - 1; i++) {
            // Only pairs that fonts draw as one ligature get swapped by this corruption.
            if (chars[i] === chars[i + 1] || !allowed(chars[i] + chars[i + 1])) continue;
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
const SEEDED = 0.5; // a pair assumed swapped without being seen in the text
const LIKELY_SWAP_COST = 3;
const CONFIRMED_SWAP_COST = 2;

// Some PDFs store a whole word in visual order: «رشنلل» for «للنشر». Reversal
// is tried only for unknown words; it is cheap when the word cannot be correct
// as written (it starts with «ة»/«ى» or ends with a reversed article «لا»).
const REVERSE_COST = 6;
const REVERSE_SIGNED_COST = 2;
const REVERSED_SHAPE = /^[ةى]|[^ا]لا$|للا$|لاو$/;

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
    ['ك', 'الىمي'],
];
const LIKELY = new Set();
for (const [firsts, seconds] of LIGATURES) {
    for (const a of firsts) for (const b of seconds) LIKELY.add(b + a);
}

// Short function words that this corruption turns into other (rare) words.
// Only applied when the text clearly has the problem.
const COMMON_SWAPS = {
    يف: 'في', هلا: 'لها', هبا: 'بها', هلم: 'لهم', هبم: 'بهم', هلن: 'لهن',
    هلذا: 'لهذا', هبذا: 'بهذا', هلذه: 'لهذه', هلل: 'لله', وهلل: 'ولله', هبذه: 'بهذه', فيام: 'فيما', بيام: 'بيما', كام: 'كما',
    // «ال» is never a word on its own; «ام»/«هب» without hamza are not used in formal text.
    ال: 'لا', ام: 'ما', هب: 'به', حتت: 'تحت', غري: 'غير', بني: 'بين', ىلع: 'على', ىلإ: 'إلى',
};

/** Article written with a swapped lam-ligature: «احلوثي» → «الحوثي», «امل…» → «الم…». */
const ARTICLE_SWAP = /^(و|ف|ب|ك)?ا([جحخمه])ل(?=[\u0621-\u064A]{2})/;

/**
 * Best correction for one word: { word, keys } or null to keep it.
 * Known words are only reconsidered when `confirmed` is given: then every
 * swapped pair must already be confirmed in the same text, and the result must
 * be much more frequent («بني» → «بين», but «طولية» stays).
 */
export function repairWord(dict, word, confirmed = null, { reconsiderKnown = false } = {}) {
    if (word.length < 2 || word.length > 16) return null;
    const original = wordRank(dict, word);
    // Very rare entries are often the corrupted form itself (copied from PDFs into
    // the source corpora, e.g. «هيكيل»): treat them as unknown, but only replace
    // them with a much more frequent word.
    // Words known only through a one-letter prefix («كربى» = «ك» + «ربى») are
    // weak evidence: treat them like rare words.
    // «فاكتب» (ف + imperative) is a normal form, so a bare alef after the prefix is not weak.
    const weak = original !== Infinity && !dict.has(word) && word[1] !== 'ا'
        && wordRank(dict, word, { oneLetter: false }) === Infinity;
    const rare = original !== Infinity && (original > RARE_RANK || weak);
    const known = original !== Infinity && !rare;
    // Known only through a prefix («بتكلا» = «ب» + a rare word) but shaped like a
    // reversed word: take the reversal when it is far more frequent.
    if (word.length >= 4 && !dict.has(word) && REVERSED_SHAPE.test(word) && !reconsiderKnown) {
        const rev = Array.from(word).reverse().join('');
        const r = wordRank(dict, rev);
        if (r <= RARE_RANK && (r + 1) * 20 < original) return { word: rev, keys: ['rev'] };
    }
    if (known && !reconsiderKnown) return null;

    const bases = [{ word, keys: [] }];
    // «ألربعني» for «الأربعين»: the article lost its alef and lam-hamza was swapped.
    if (/^[أإآ]ل/.test(word)) bases.push({ word: 'ال' + word[0] + word.slice(2), keys: ['ال'] });

    let best = null;
    let bestScore = Infinity;
    for (const base of bases) {
        const variants = base.keys.length ? [base] : [];
        const allowed = (k) => LIKELY.has(k) || (confirmed?.get(k) ?? 0) >= 2;
        for (const v of swapVariants(base.word, allowed)) variants.push({ word: v.word, keys: [...base.keys, ...v.keys] });
        if (!known && base.keys.length === 0 && word.length >= 4) {
            variants.push({ word: Array.from(word).reverse().join(''), keys: ['rev'] });
        }
        for (const v of variants) {
            if (v.word === word) continue;
            const count = (k) => confirmed?.get(k) ?? 0;
            const seen = (k) => count(k) >= 2;
            // Known words: every swap must be confirmed in this text (once is enough for ligature pairs).
            if (known && !v.keys.every((k) => seen(k) || (LIKELY.has(k) && count(k) > 0))) continue;
            // A correct word starting with the article keeps it («الفردي» never becomes «لافردي»).
            if (known && word.startsWith('ال') && !v.word.startsWith('ال')) continue;
            const r = wordRank(dict, v.word);
            if (r === Infinity) continue;
            // Turning a leading article into «لا» needs a common word («العب» → «لاعب», not «لابحاث»).
            if (word.startsWith('ال') && v.word.startsWith('لا') && r > 20000) continue;
            const cost = (k) => k === 'rev'
                ? (seen(k) || REVERSED_SHAPE.test(word) ? REVERSE_SIGNED_COST : REVERSE_COST)
                : seen(k) ? CONFIRMED_SWAP_COST : LIKELY.has(k) ? LIKELY_SWAP_COST : SWAP_COST;
            const score = (r + 1) * v.keys.reduce((c, k) => c * cost(k), 1);
            if (score < bestScore) {
                best = v;
                bestScore = score;
            }
        }
    }
    // Unknown word (often a name) with no dictionary match: apply swaps that
    // cannot occur in correct text: the swapped article and a medial «ى».
    if (!best && !known && confirmed) {
        let w = word.replace(ARTICLE_SWAP, (_, p = '', c) => p + 'ال' + c);
        w = w.replace(/ى(?=[\u0621-\u064A])(.)/g, (_, c) => c + 'ى');
        if (w !== word) return { word: w, keys: [] };
    }

    if (best && rare && (wordRank(dict, best.word) + 1) * 10 > original) return null;

    // A known word is replaced only by a more frequent one, and only through pairs
    // this text is already known to swap (checked above).
    // Pairs only assumed swapped need a much more frequent word («تفعيل» stays).
    if (best && known) {
        const factor = best.keys.some((k) => (confirmed?.get(k) ?? 0) < 1) ? 3 : 1;
        if (wordRank(dict, best.word) * factor >= original) return null;
    }
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
        if (cache.get(w) || isRareOrUnknown(dict, w)) cache.set(w, repairWord(dict, w, confirmed));
    }
    // Pass 3: known words, only when the text clearly has the problem.
    if (aggressive) {
        // Lam ligatures are always stored swapped by these producers.
        // Not «ال»/«أل»: alef-lam also occurs inside correct words («ثالث», «سأل»).
        for (const k of LIKELY) {
            if (k[1] === 'ل' && !'اأإآ'.includes(k[0]) && !confirmed.get(k)) confirmed.set(k, SEEDED);
        }
        for (const w of words) {
            if (cache.get(w) || wordRank(dict, w) === Infinity) continue;
            if (COMMON_SWAPS[w]) cache.set(w, { word: COMMON_SWAPS[w], keys: [], common: true });
            // Other two-letter words are too ambiguous to swap («رب» / «بر»).
            else if (w.length > 2) cache.set(w, repairWord(dict, w, confirmed, { reconsiderKnown: true }));
        }
    }

    // «إلجراء»: when this text writes the article with its alef («اإلجراء»), a
    // word starting «إل»/«أل» without it is the preposition («لإجراء», «لأنه»),
    // unless the same word also appears with the alef («األربعني» / «ألربعني»).
    if (words.some((w) => /^ا[أإ]ل/.test(w))) {
        const set = new Set(words);
        for (const w of words) {
            const r = cache.get(w);
            if (/^[أإ]ل/.test(w) && r?.word === 'ال' + w[0] + w.slice(2) && !set.has('ا' + w)) {
                cache.set(w, { word: 'ل' + w[0] + r.word.slice(3), keys: r.keys });
            }
        }
    }

    // Split into parts so the UI can highlight changed words.
    const parts = [];
    let fixed = 0;
    let last = 0;
    let prevOut = '';
    for (const m of text.matchAll(TOKEN)) {
        let r = cache.get(bare(m[0]));
        // «الـ» (article with tatweel) is not the word «ال».
        if (r?.common && text[m.index + m[0].length] === '\u0640') r = null;
        // «هل» opens questions; elsewhere in corrupted text it is «له».
        if (aggressive && bare(m[0]) === 'هل' && !/(^|[.!?؟:\n«"(]\s*)$/.test(text.slice(Math.max(0, m.index - 3), m.index))) {
            r = { word: 'له', keys: [] };
        }
        if (r && aggressive) r = inContext(prevOut, m, r);
        prevOut = r?.word ?? bare(m[0]);
        if (!r || r.word === bare(m[0])) continue;
        if (m.index > last) parts.push({ text: text.slice(last, m.index) });
        parts.push({ text: r.word, from: m[0] });
        last = m.index + m[0].length;
        fixed++;
    }
    if (last < text.length) parts.push({ text: text.slice(last) });
    return { text: parts.map((p) => p.text).join(''), fixed, parts };
}

/** Fixed phrases and grammar that settle a choice frequency cannot. */
function inContext(prev, m, r) {
    // «رب العاملني» is «رب العالمين», not «العاملين».
    if (/^(رب|ربّ)$/.test(prev) && r.word === 'العاملين') return { ...r, word: 'العالمين' };
    // «الجزء الثالث»: an ordinal after a definite singular noun stays as written.
    if (/^(و|ف|ب|ك|ل)?(ال)?ثالث(ة)?$/.test(m[0]) && /^ال/.test(prev) && !/(ات|ين|ون)$/.test(prev)) return null;
    return r;
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
