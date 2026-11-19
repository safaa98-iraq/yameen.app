// Alpine components for the text tools. Each tool loads its text library on
// demand, so pages without tools never download it.
import { copyText } from './lib/clipboard';
import { CLEAN_DEFAULTS } from './text/clean-defaults';
import { loadFont } from './lib/font-loader';

const COPIED_MS = 1500;

function copyable() {
    return {
        copied: false,
        // Copy `text` after charging one credit for the source text (deduplicated per text per day).
        async copy(text, action = 'fix') {
            if (!text) return;
            if (action && !(await this.$store.credits.consume(action, this.$store.text.value))) return;
            if (await copyText(text)) {
                this.copied = true;
                clearTimeout(this._copiedTimer);
                this._copiedTimer = setTimeout(() => (this.copied = false), COPIED_MS);
            }
        },
    };
}

export function registerTools(Alpine) {
    Alpine.data('fixer', (initial = {}) => ({
        ...copyable(),
        opts: { stripTashkeel: true, shape: true, reverse: true },
        fonts: initial.fonts ?? [],
        font: initial.font ?? null, // slug
        lib: null,
        async init() {
            // ?font=slug also works on statically exported pages.
            const fromUrl = new URLSearchParams(window.location.search).get('font');
            if (fromUrl && this.fonts.some((f) => f.slug === fromUrl)) this.font = fromUrl;
            this.$watch('font', () => this.loadSelected());
            this.loadSelected();
            this.lib = await import('./text/fixer');
        },
        get selected() {
            return this.fonts.find((f) => f.slug === this.font) ?? null;
        },
        loadSelected() {
            if (this.selected) loadFont(this.selected, 400);
        },
        get fontFamily() {
            return this.selected ? `'${this.selected.family}', var(--font-ar)` : 'var(--font-ar)';
        },
        get output() {
            return this.lib ? this.lib.fixText(this.$store.text.value, this.opts) : '';
        },
        // The input was already fixed (or copied from a PDF/app) and is restored first.
        get restored() {
            return Boolean(this.lib?.hasPresentationForms(this.$store.text.value));
        },
    }));

    // App picker: type, then click your app. The click copies the fixed text
    // and shows how to paste it into that app.
    Alpine.data('appPicker', (apps = []) => ({
        apps,
        picked: null,
        copiedText: '',
        empty: false,
        init() {
            this.$watch('$store.text.value', () => {
                this.picked = null;
                this.empty = false;
            });
        },
        get app() {
            return this.apps.find((a) => a.slug === this.picked) ?? null;
        },
        async pick(slug) {
            const text = this.$store.text.value.trim();
            if (!text) {
                this.empty = true;
                this.$refs.input?.focus();
                return;
            }
            if (!(await this.$store.credits.consume('fix', text))) return;
            const { fixText } = await import('./text/fixer');
            const fixed = fixText(this.$store.text.value);
            if (await copyText(fixed)) {
                this.copiedText = fixed;
                this.picked = slug;
                this.empty = false;
                this.$nextTick(() => this.$refs.done?.scrollIntoView({ block: 'nearest', behavior: 'smooth' }));
            }
        },
    }));

    Alpine.data('pdfFixer', () => {
        // Last computed result, outside Alpine's reactive data so caching it
        // inside a getter doesn't trigger another render.
        let memo = null;
        return {
        ...copyable(),
        lib: null,
        words: null, // swapped-pair repair (pdf-words.js)
        dict: null,
        dictState: 'idle', // idle | loading | ready | failed
        repairWords: true,
        force: null, // null = automatic detection, true/false = user override
        async init() {
            [this.lib, this.words] = await Promise.all([import('./text/pdf'), import('./text/pdf-words')]);
            this.$watch('$store.text.value', () => {
                this.force = null;
                this.ensureDictionary();
            });
            this.ensureDictionary();
        },
        get swapped() {
            return Boolean(this.words?.hasSwappedPairs(this.$store.text.value));
        },
        // The word list (~850 KB) is only downloaded for text that needs it.
        async ensureDictionary() {
            if (this.dictState !== 'idle' || !this.swapped) return;
            this.dictState = 'loading';
            try {
                const { default: url } = await import('./data/ar-words.txt?url');
                const response = await fetch(url);
                if (!response.ok) throw new Error(String(response.status));
                this.dict = this.words.buildDictionary(await response.text());
                this.dictState = 'ready';
            } catch {
                this.dictState = 'failed';
            }
        },
        get result() {
            const empty = { text: '', detection: { reversed: null }, reversedApplied: false, wordsFixed: 0 };
            if (!this.lib) return empty;
            const text = this.$store.text.value;
            const key = [text, this.force, this.repairWords, this.dictState].join('\u0000');
            if (memo?.key === key) return memo.value;

            // Text with swapped pairs is already in logical order: don't reverse it automatically.
            const swapped = this.swapped;
            const reverse = this.force ?? (swapped ? false : 'auto');
            let r = { ...this.lib.fixPdfText(text, { reverse }), wordsFixed: 0 };
            if (swapped && this.repairWords && this.dict) {
                const lines = this.words.repairLines(r.text, this.dict);
                const w = this.words.repairSwappedWords(lines, this.dict, { aggressive: true });
                r = { ...r, text: w.text, wordsFixed: w.fixed };
            }
            memo = { key, value: r };
            return r;
        },
        get status() {
            const r = this.result;
            if (!this.$store.text.value.trim()) return 'empty';
            if (this.force !== null) return r.reversedApplied ? 'manual-reversed' : 'manual-kept';
            if (r.reversedApplied) return 'auto-reversed';
            if (this.swapped) return 'logical';
            return r.detection.reversed === null ? 'unsure' : 'logical';
        },
        toggleReverse() {
            this.force = !this.result.reversedApplied;
        },
        };
    });

    // Garbled PDF text: one step, no buttons. Reverses visual order, restores
    // letters, repairs swapped pairs, joins lines; highlights changed words.
    Alpine.data('garbledFixer', () => {
        let memo = null;
        return {
            ...copyable(),
            lib: null,
            words: null,
            dict: null,
            dictState: 'idle', // idle | loading | ready | failed
            join: true,
            async init() {
                [this.lib, this.words] = await Promise.all([import('./text/garbled'), import('./text/pdf-words')]);
                this.loadDictionary();
            },
            async loadDictionary() {
                if (this.dictState !== 'idle') return;
                this.dictState = 'loading';
                try {
                    const { default: url } = await import('./data/ar-words.txt?url');
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(String(response.status));
                    this.dict = this.words.buildDictionary(await response.text());
                    this.dictState = 'ready';
                } catch {
                    this.dictState = 'failed';
                }
            },
            get result() {
                const text = this.$store.text.value;
                if (!this.lib || !text.trim()) return { text: '', parts: [], fixed: 0, reversed: false };
                const key = [text, this.dictState, this.join].join('\u0000');
                if (memo?.key === key) return memo.value;
                const value = this.lib.fixGarbledText(text, this.dict, { join: this.join });
                memo = { key, value };
                return value;
            },
        };
    });

    Alpine.data('cleaner', () => ({
        ...copyable(),
        lib: null,
        opts: { ...CLEAN_DEFAULTS },
        async init() {
            this.lib = await import('./text/cleaner');
        },
        get output() {
            return this.lib ? this.lib.cleanText(this.$store.text.value, this.opts) : '';
        },
    }));
}
