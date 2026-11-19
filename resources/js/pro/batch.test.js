import { describe, expect, it } from 'vitest';
import { detectType, inspect, process } from './batch';
import { fixText } from '../text/fixer';

describe('batch processing', () => {
    it('detects supported types', () => {
        expect(detectType('a.SRT')).toBe('srt');
        expect(detectType('a.docx')).toBe(null);
    });

    it('fixes SRT text lines and keeps numbers and timings', () => {
        const srt = '1\r\n00:00:01,000 --> 00:00:02,000\r\nمرحبا\r\nبكم\r\n\r\n2\r\n00:00:03,000 --> 00:00:04,000\r\nأهلا\r\n';
        const out = process('srt', srt);
        expect(out).toBe(`1\r\n00:00:01,000 --> 00:00:02,000\r\n${fixText('مرحبا')}\r\n${fixText('بكم')}\r\n\r\n2\r\n00:00:03,000 --> 00:00:04,000\r\n${fixText('أهلا')}\r\n`);
    });

    it('fixes only the chosen CSV columns', () => {
        const csv = 'id,title,note\n1,مرحبا,keep\n2,"أهلا, وسهلا",x';
        expect(inspect('csv', csv).fields).toEqual(['id', 'title', 'note']);
        const out = process('csv', csv, { fields: ['title'] });
        expect(out).toBe(`id,title,note\n1,${fixText('مرحبا')},keep\n2,"${fixText('أهلا, وسهلا')}",x`);
    });

    it('fixes only the chosen JSON keys and keeps the structure', () => {
        const json = '{\n    "title": "مرحبا",\n    "items": [{"label": "أهلا", "id": "x1"}]\n}\n';
        expect(inspect('json', json).fields.sort()).toEqual(['id', 'label', 'title']);
        const out = JSON.parse(process('json', json, { fields: ['label'] }));
        expect(out).toEqual({ title: 'مرحبا', items: [{ label: fixText('أهلا'), id: 'x1' }] });
        expect(JSON.parse(process('json', json, { fields: [] }))).toEqual(JSON.parse(json));
        expect(process('json', json, { fields: [] }).startsWith('{\n    "title"')).toBe(true); // indentation kept
    });

    it('fixes TXT as a whole', () => {
        expect(process('txt', 'سطر\nآخر')).toBe(fixText('سطر\nآخر'));
    });
});
