// One-step repair of Arabic text copied from PDFs: whatever went wrong, return
// readable logical text. Combines the PDF fix (presentation forms, reversed
// lines) with the swapped-pair word repair, and optionally joins hard-wrapped lines.
import { fixPdfText } from './pdf';
import { repairLines, repairSwappedWords, swapSignals } from './pdf-words';

/** Join lines broken by the PDF layout; blank lines still separate paragraphs. */
export function joinLines(text) {
    return text
        .split(/\n\s*\n/)
        .map((para) => para.split('\n').map((l) => l.trim()).filter(Boolean).join(' '))
        .join('\n\n');
}

/**
 * @param {string} text
 * @param {Map<string, number>|null} dict  frequency dictionary (null: skip word repair)
 * @param {{ join?: boolean }} options
 * @returns {{ text: string, parts: {text: string, from?: string}[], fixed: number, reversed: boolean }}
 */
export function fixGarbledText(text, dict, { join = true } = {}) {
    if (!text.trim()) return { text: '', parts: [], fixed: 0, reversed: false };

    // 1. Visual order and special letter forms (detected automatically).
    const pdf = fixPdfText(text, { reverse: 'auto' });
    let out = pdf.text;

    // 2. Layout debris and mirrored brackets, then joined lines.
    if (dict) out = repairLines(out, dict);
    if (join) out = joinLines(out);

    // 3. Letter pairs swapped inside words.
    if (!dict) return { text: out, parts: [{ text: out }], fixed: 0, reversed: pdf.reversedApplied };
    const words = repairSwappedWords(out, dict, { aggressive: swapSignals(out) >= 1 });
    return { ...words, reversed: pdf.reversedApplied };
}
