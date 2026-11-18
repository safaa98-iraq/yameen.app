import { describe, expect, it } from 'vitest';
import { cleanText } from './cleaner';

const none = { tashkeel: false, tatweel: false, alef: false, yaMaqsura: false, digits: 'none', spaces: false, trimLines: false };

describe('cleanText', () => {
    it('removes diacritics', () => {
        expect(cleanText('بِسْمِ اللَّهِ', { ...none, tashkeel: true })).toBe('بسم الله');
    });

    it('removes tatweel', () => {
        expect(cleanText('جـــميل', { ...none, tatweel: true })).toBe('جميل');
    });

    it('unifies alef forms', () => {
        expect(cleanText('أحمد إبراهيم آمن ٱلله', { ...none, alef: true })).toBe('احمد ابراهيم امن الله');
    });

    it('unifies alef maqsura to ya only when asked', () => {
        expect(cleanText('على', none)).toBe('على');
        expect(cleanText('على', { ...none, yaMaqsura: true })).toBe('علي');
    });

    it('converts digits in both directions', () => {
        expect(cleanText('٢٠٢٦ و ۱۲', { ...none, digits: 'western' })).toBe('2026 و 12');
        expect(cleanText('2026', { ...none, digits: 'arabic' })).toBe('٢٠٢٦');
    });

    it('collapses spaces and trims lines without touching line breaks', () => {
        expect(cleanText('  نص   مع \t مسافات  \n\n  سطر  ', { ...none, spaces: true, trimLines: true })).toBe('نص مع مسافات\n\nسطر');
    });

    it('applies sensible defaults', () => {
        expect(cleanText(' مَرحـبا  ')).toBe('مرحبا');
        expect(cleanText('')).toBe('');
    });
});
