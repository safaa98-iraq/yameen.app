// Text cleaner: independent, composable normalizations.
import { DIACRITICS } from './arabic';

export const CLEAN_DEFAULTS = Object.freeze({
    tashkeel: true,
    tatweel: true,
    alef: false,
    yaMaqsura: false,
    digits: 'none', // 'none' | 'western' | 'arabic'
    spaces: true,
    trimLines: true,
});

const ARABIC_INDIC = '٠١٢٣٤٥٦٧٨٩';
const PERSIAN = '۰۱۲۳۴۵۶۷۸۹';

export function cleanText(text, options = {}) {
    const o = { ...CLEAN_DEFAULTS, ...options };
    if (!text) return '';
    let out = text;

    if (o.tashkeel) out = out.replace(DIACRITICS, '');
    if (o.tatweel) out = out.replace(/ـ/g, '');
    if (o.alef) out = out.replace(/[أإآٱ]/g, 'ا');
    if (o.yaMaqsura) out = out.replace(/ى/g, 'ي');

    if (o.digits === 'western') {
        out = out.replace(/[٠-٩]/g, (d) => String(ARABIC_INDIC.indexOf(d))).replace(/[۰-۹]/g, (d) => String(PERSIAN.indexOf(d)));
    } else if (o.digits === 'arabic') {
        out = out.replace(/[0-9]/g, (d) => ARABIC_INDIC[d]).replace(/[۰-۹]/g, (d) => ARABIC_INDIC[PERSIAN.indexOf(d)]);
    }

    if (o.spaces) out = out.replace(/[ \t ]{2,}/g, ' ');
    if (o.trimLines) out = out.replace(/^[ \t ]+|[ \t ]+$/gm, '');

    return out;
}
