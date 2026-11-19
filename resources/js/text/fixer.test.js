import { describe, expect, it } from 'vitest';
import { fixText } from './fixer';
import { shape } from './shaper';

// Helper: build strings from code points so expectations are unambiguous.
const u = (...cps) => String.fromCodePoint(...cps);

describe('fixText', () => {
    it('shapes and reverses a plain word', () => {
        // مرحبا: meem initial, ra final, hah initial, beh medial, alef final
        expect(fixText('مرحبا')).toBe(u(0xfe8e, 0xfe92, 0xfea3, 0xfeae, 0xfee3));
    });

    it('fixes a sentence and reverses word order', () => {
        // سلام عليكم
        const salam = u(0x0645, 0xfefc, 0xfeb3);
        const alaykum = u(0xfee2, 0xfedc, 0xfef4, 0xfee0, 0xfecb);
        expect(fixText('سلام عليكم')).toBe(`${alaykum} ${salam}`);
    });

    it('uses lam-alef ligatures in isolated and final position', () => {
        expect(fixText("لا")).toBe(u(0xfefb));
        expect(shape('سلام')).toBe(u(0xfeb3, 0xfefc, 0x0645));
        // الأمل: alef, lam+hamza-alef (isolated, alef does not join forward), meem, lam
        expect(shape('الأمل')).toBe(u(0x0627, 0xfef7, 0xfee3, 0xfede));
        expect(shape('إلى')).toBe(u(0x0625, 0xfedf, 0xfef0));
    });

    it('handles taa marbuta', () => {
        // مدرسة
        expect(fixText('مدرسة')).toBe(u(0xfe94, 0xfeb3, 0x0631, 0xfeaa, 0xfee3));
    });

    it('handles hamza forms', () => {
        expect(fixText('سماء')).toBe(u(0x0621, 0xfe8e, 0xfee4, 0xfeb3));
        expect(shape('مسؤول')).toBe(u(0xfee3, 0xfeb4, 0xfe86, 0x0648, 0x0644));
        expect(shape('قائد')).toBe(u(0xfed7, 0xfe8e, 0xfe8b, 0xfeaa));
        expect(shape('إسلام')).toBe(u(0x0625, 0xfeb3, 0xfefc, 0x0645));
        expect(shape('آمن')).toBe(u(0x0622, 0xfee3, 0xfee6));
    });

    it('keeps Latin words in left-to-right order inside Arabic text', () => {
        const ana = u(0xfe8e, 0xfee7, 0x0623);
        const uhibb = u(0xfe90, 0xfea3, 0x0623);
        expect(fixText('أنا أحب Photoshop')).toBe(`Photoshop ${uhibb} ${ana}`);
    });

    it('keeps numbers in left-to-right order', () => {
        expect(fixText('عام 2026')).toBe(`2026 ${u(0x0645, 0xfe8e, 0xfecb)}`);
        expect(fixText('السعر 12.5 دينار')).toContain('12.5');
    });

    it('places sentence punctuation correctly', () => {
        const marhaba = u(0xfe8e, 0xfe92, 0xfea3, 0xfeae, 0xfee3);
        expect(fixText('مرحبا!')).toBe(`!${marhaba}`);
        expect(fixText('مرحبا؟')).toBe(`؟${marhaba}`);
        // Arabic comma follows the first word, i.e. sits to its left visually.
        const alam = u(0xfee2, 0xfedf, 0xfe8e, 0xfecb);
        expect(fixText('مرحبا، عالم')).toBe(`${alam} ،${marhaba}`);
    });

    it('mirrors brackets in right-to-left runs', () => {
        expect(fixText('(نص)')).toBe(`(${u(0xfeba, 0xfee7)})`);
    });

    it('processes each line independently and preserves empty lines', () => {
        const salam = u(0x0645, 0xfefc, 0xfeb3);
        const marhaba = u(0xfe8e, 0xfe92, 0xfea3, 0xfeae, 0xfee3);
        expect(fixText('سلام\nمرحبا')).toBe(`${salam}\n${marhaba}`);
        expect(fixText('سلام\n\nمرحبا')).toBe(`${salam}\n\n${marhaba}`);
        expect(fixText('سلام\r\nمرحبا\r\n')).toBe(`${salam}\r\n${marhaba}\r\n`);
    });

    it('returns an empty string for empty input', () => {
        expect(fixText('')).toBe('');
        expect(fixText('\n')).toBe('\n');
    });

    it('removes diacritics by default', () => {
        expect(fixText('مَرْحَبًا')).toBe(fixText('مرحبا'));
        expect(fixText('السَّلامُ')).toBe(fixText('السلام'));
    });

    it('keeps diacritics after their base letter when stripping is off', () => {
        // مَن → visual: noon final, then meem initial followed by its fatha
        expect(fixText('مَن', { stripTashkeel: false })).toBe(u(0xfee6, 0xfee3, 0x064e));
        // a mark between lam and alef stays after the ligature
        expect(shape("لَا")).toBe(u(0xfefb, 0x064e));
    });

    it('respects the option toggles', () => {
        expect(fixText('مرحبا', { shape: false, reverse: false })).toBe('مرحبا');
        expect(fixText('مرحبا', { reverse: false })).toBe(u(0xfee3, 0xfeae, 0xfea3, 0xfe92, 0xfe8e));
        expect(fixText('مرحبا', { shape: false })).toBe('ابحرم');
    });

    it('leaves Latin-only lines unchanged', () => {
        expect(fixText('Hello World 42')).toBe('Hello World 42');
    });

    it('breaks joining at ZWNJ and removes it', () => {
        expect(fixText('ب‌ب')).toBe(u(0x0628, 0x0628));
    });

    it('joins through tatweel', () => {
        expect(fixText('بـب')).toBe(u(0xfe90, 0x0640, 0xfe91));
    });

    it('shapes Persian and Kurdish letters', () => {
        expect(fixText('گل')).toBe(u(0xfede, 0xfb94));
        expect(shape('پی')).toBe(u(0xfb58, 0xfbfd));
    });

    it('keeps isolated letters as base letters so fonts without isolated forms still work', () => {
        expect(shape('و')).toBe('و');
        expect(shape('ب ب')).toBe('ب ب');
        expect(shape('دار')).toBe('دار'); // nothing joins
        expect(shape('باب')).toBe(u(0xfe91, 0xfe8e, 0x0628)); // beh initial, alef final, beh isolated
    });

    it('treats hamza as non-joining', () => {
        // شيء: sheen initial, yeh final, hamza isolated
        expect(shape('شيء')).toBe(u(0xfeb7, 0xfef2, 0x0621));
    });

    it('restores already-fixed input before fixing, so fixing twice changes nothing', () => {
        for (const s of ['السلام عليكم ورحمة الله', 'مرحبا بكم في يمين 2026', 'أهلا وسهلا', 'أنا أحب Photoshop']) {
            const once = fixText(s);
            expect(fixText(once)).toBe(once);
        }
    });

    it('fixes text copied out of an app without Arabic support (isolated forms, reversed)', () => {
        expect(fixText('\uFE8D\uFEDD\uFEE9\uFEB1\uFEED \uFE8D\uFEDD\uFEE9\uFE83')).toBe(fixText('أهلا وسهلا'));
    });
});
