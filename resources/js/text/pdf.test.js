import { describe, expect, it } from 'vitest';
import { detectReversed, fixPdfText, normalizePresentationForms } from './pdf';
import { fixText } from './fixer';

describe('normalizePresentationForms', () => {
    it('maps presentation forms back to base letters', () => {
        expect(normalizePresentationForms('ﻣﺮﺣﺒﺎ')).toBe('مرحبا');
    });

    it('decomposes lam-alef ligatures', () => {
        expect(normalizePresentationForms('ﺳﻼﻡ')).toBe('سلام');
    });

    it('keeps isolated harakat as marks without a space', () => {
        expect(normalizePresentationForms('ﺏﹶ')).toBe('بَ');
    });

    it('leaves Latin, digits and base Arabic untouched', () => {
        expect(normalizePresentationForms('PDF 2026 مرحبا')).toBe('PDF 2026 مرحبا');
    });
});

describe('detectReversed', () => {
    it('detects visual order from presentation forms', () => {
        expect(detectReversed(fixText('السلام عليكم ورحمة الله')).reversed).toBe(true);
    });

    it('detects logical order from presentation forms', () => {
        expect(detectReversed(fixText('السلام عليكم ورحمة الله', { reverse: false })).reversed).toBe(false);
    });

    it('detects reversed base letters from word patterns', () => {
        expect(detectReversed('ةبتكملا ىلإ تبهذ').reversed).toBe(true);
        expect(detectReversed('ذهبت إلى المكتبة').reversed).toBe(false);
    });

    it('treats text made only of isolated forms as copied from an app (visual order)', () => {
        // "أهلا وسهلا" as shown by an app without Arabic support
        const broken = '\uFE8D\uFEDD\uFEE9\uFEB1\uFEED \uFE8D\uFEDD\uFEE9\uFE83';
        expect(detectReversed(broken).reversed).toBe(true);
        expect(fixPdfText(broken).text).toBe('أهلا وسهلا');
    });

    it('is unsure on very short text', () => {
        expect(detectReversed('من').reversed).toBe(null);
    });
});

describe('fixPdfText', () => {
    it('round-trips text produced by the fixer', () => {
        const source = 'السلام عليكم 2026\nأهلاً بك في Photoshop';
        const expected = 'السلام عليكم 2026\nأهلا بك في Photoshop';
        expect(fixPdfText(fixText(source)).text).toBe(expected);
    });

    it('only normalizes when order is already logical', () => {
        const shaped = fixText('مرحبا بكم في المدرسة', { reverse: false });
        const result = fixPdfText(shaped);
        expect(result.reversedApplied).toBe(false);
        expect(result.text).toBe('مرحبا بكم في المدرسة');
    });

    it('can force or skip reversal', () => {
        expect(fixPdfText('ابحرم', { reverse: true }).text).toBe('مرحبا');
        expect(fixPdfText(fixText('مرحبا بكم'), { reverse: false }).reversedApplied).toBe(false);
    });

    it('handles empty input', () => {
        expect(fixPdfText('').text).toBe('');
    });
});
