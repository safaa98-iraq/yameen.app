import { describe, expect, it } from 'vitest';
import { readFileSync } from 'node:fs';
import { buildDictionary, hasSwappedPairs, repairLines, repairSwappedWords, fixMirroredBrackets } from './pdf-words';

const dict = buildDictionary(readFileSync(new URL('../data/ar-words.txt', import.meta.url), 'utf8'));
const repair = (t) => repairSwappedWords(t, dict, { aggressive: hasSwappedPairs(t) }).text;

describe('swapped letter pairs from PDFs', () => {
    it('detects the problem only in corrupted text', () => {
        expect(hasSwappedPairs('يقع المتحف يف وسط المدينة عىل ضفة النهر')).toBe(true);
        expect(hasSwappedPairs('يقع المتحف في وسط المدينة على ضفة النهر')).toBe(false);
    });

    it('repairs common corrupted words', () => {
        expect(repair('يقع المتحف يف وسط المدينة عىل ضفة النهر')).toBe('يقع المتحف في وسط المدينة على ضفة النهر');
    });

    it('repairs ligatures with lam-alef, the article and final ya-noon', () => {
        const input = 'زيارة ألربعني يف حمافظة كربالء من أكرب التجمعات اجلماهريية عىل وجه األرض خالل الفترة';
        expect(repair(input)).toBe('زيارة الأربعين في محافظة كربلاء من أكبر التجمعات الجماهيرية على وجه الأرض خلال الفترة');
    });

    it('fixes known words only through pairs confirmed in the same text', () => {
        const input = 'عدد املراجعني الطبيني بني عامي 2017 و2024 من بيانات طولية عىل مدى ثامين سنوات';
        expect(repair(input)).toBe('عدد المراجعين الطبيين بين عامي 2017 و2024 من بيانات طولية على مدى ثماني سنوات');
    });

    it('keeps the article on correct words', () => {
        expect(repair('حقق االنحدار اخلطي أفضل أداء يف حتليل االستبعاد الفردي')).toBe('حقق الانحدار الخطي أفضل أداء في تحليل الاستبعاد الفردي');
    });

    it('reports only words that actually changed', () => {
        const r = repairSwappedWords('مما يوفر معامل حتويل يف النص عىل الأقل', dict, { aggressive: true });
        expect(r.text).toBe('مما يوفر معامل تحويل في النص على الأقل');
        expect(r.parts.filter((p) => p.from).map((p) => p.from)).toEqual(['حتويل', 'يف', 'عىل']);
        expect(r.fixed).toBe(3);
    });

    it('fixes function words that are only valid one way in formal text', () => {
        expect(repair('ال يمكن حتت أي ظرف عىل اإلطالق')).toBe('لا يمكن تحت أي ظرف على الإطلاق');
    });

    it('reads «هل» as a question only at the start of a sentence', () => {
        expect(repair('هل قرأت الكتاب؟ قدم هل يف املوعد')).toBe('هل قرأت الكتاب؟ قدم له في الموعد');
    });

    it('keeps «الـ» with tatweel', () => {
        expect(repair('أداة الـ PDF يف النص عىل الأقل')).toBe('أداة الـ PDF في النص على الأقل');
    });

    it('fixes the swapped article on names missing from the dictionary', () => {
        expect(repair('صرح املتحدث احلوثي يف بيان عىل موقع')).toBe('صرح المتحدث الحوثي في بيان على موقع');
    });

    it('leaves correct text untouched', () => {
        const ok = 'استخدمت بيانات طولية من النشرات الإحصائية الرسمية لمحافظة كربلاء بين عامي 2017 و2024';
        expect(repair(ok)).toBe(ok);
    });

    it('fixes mirrored brackets', () => {
        expect(fixMirroredBrackets(')2017-2024(، شملت )الإجمالي: 22 مليون(')).toBe('(2017-2024)، شملت (الإجمالي: 22 مليون)');
        expect(fixMirroredBrackets('(نص سليم)')).toBe('(نص سليم)');
        // pairs spanning lines
        expect(fixMirroredBrackets(')معامل p <\n0.0001(. أداء )RMSE\n0.980(')).toBe('(معامل p <\n0.0001). أداء (RMSE\n0.980)');
    });

    it('restores words stored fully reversed', () => {
        expect(repair('يخطر أصحاب الابحاث المقبولة رشنلل موافقة هيأة التحرير عىل نشرها')).toBe('يخطر أصحاب الابحاث المقبولة للنشر موافقة هيأة التحرير على نشرها');
        expect(repair('قرأت بتكلا عن ةلودلا')).toBe('قرأت الكتب عن الدولة');
        // real words and short words are never reversed
        expect(repair('مثلا فعلا إلا كلا يونس محمد')).toBe('مثلا فعلا إلا كلا يونس محمد');
    });

    it('reads «إل» as «لإ» when the text keeps the article alef', () => {
        expect(repair('مع املالحظات املحددة إلجراء التعديالت عىل األبحاث')).toBe('مع الملاحظات المحددة لإجراء التعديلات على الأبحاث');
        // the same word also written with the alef: it is the article
        expect(repair('زيارة ألربعني يف كربالء وزيارة األربعني')).toBe('زيارة الأربعين في كربلاء وزيارة الأربعين');
    });

    it('drops debris lines and moves a stray first letter back', () => {
        expect(repairLines('سطر أول\nً\nؤدي هذا التجمع إلى طلب شديد ت\nً.', dict)).toBe('سطر أول\nتؤدي هذا التجمع إلى طلب شديد');
    });
});
