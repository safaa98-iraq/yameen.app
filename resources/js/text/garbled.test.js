import { describe, expect, it } from 'vitest';
import { readFileSync } from 'node:fs';
import { buildDictionary } from './pdf-words';
import { fixGarbledText, joinLines } from './garbled';
import { fixText } from './fixer';

const dict = buildDictionary(readFileSync(new URL('../data/ar-words.txt', import.meta.url), 'utf8'));

const LINES = [
    'كان عدد المراجعين الطبيين أقوى مؤشر منفرد لنقل المرضى بسيارات الإسعاف',
    'حقق الانحدار الخطي أفضل أداء في تحليل الاستبعاد الفردي',
    'كان متوسط نسبة نقل المرضى بسيارات الإسعاف إلى المراجعين مستقرا بشكل ملحوظ على مدار السنوات',
    'بلغ متوسط توافر سيارات الإسعاف 0.85% فقط من الحد الأدنى الموصى به من قبل منظمة الصحة العالمية',
];

describe('fixGarbledText', () => {
    it('restores text copied in visual order with presentation forms, without any button', () => {
        const copied = LINES.map((l) => fixText(l)).join('\n'); // what some PDF viewers produce
        const r = fixGarbledText(copied, dict);
        expect(r.reversed).toBe(true);
        expect(r.text).toBe(LINES.join(' '));
    });

    it('keeps percent signs after their numbers', () => {
        const line = '0.85% فقط من الحد الأدنى و 12% من الباقي';
        expect(fixGarbledText(fixText(line), dict).text).toBe(line);
    });

    it('restores visual order with isolated forms (letters shown separately)', () => {
        const copied = fixText(LINES[0], { shape: false }); // reversed base letters
        expect(fixGarbledText(copied, dict).text).toBe(LINES[0]);
    });

    it('repairs swapped pairs in logical text without reversing it', () => {
        const r = fixGarbledText('كان عدد املراجعني الطبيني أقوى مؤرش منفرد لنقل املرىض بسيارات اإلسعاف', dict);
        expect(r.reversed).toBe(false);
        expect(r.text).toBe(LINES[0]);
    });

    it('keeps line breaks when joining is off and paragraphs when it is on', () => {
        expect(fixGarbledText('سطر أول\nسطر ثان', dict, { join: false }).text).toBe('سطر أول\nسطر ثان');
        expect(joinLines('سطر أول\nتكملة\n\nفقرة ثانية')).toBe('سطر أول تكملة\n\nفقرة ثانية');
    });
});
