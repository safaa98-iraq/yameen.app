// Alpine components for the text tools. Each tool loads its text library on
// demand, so pages without tools never download it.
import { copyText } from './lib/clipboard';
import { CLEAN_DEFAULTS } from './text/clean-defaults';
import { loadFont } from './lib/font-loader';

const COPIED_MS = 1500;

function copyable() {
    return {
        copied: false,
        async copy(text) {
            if (!text) return;
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
    }));

    Alpine.data('pdfFixer', () => ({
        ...copyable(),
        lib: null,
        force: null, // null = automatic detection, true/false = user override
        async init() {
            this.lib = await import('./text/pdf');
            this.$watch('$store.text.value', () => (this.force = null));
        },
        get result() {
            if (!this.lib) return { text: '', detection: { reversed: null }, reversedApplied: false };
            return this.lib.fixPdfText(this.$store.text.value, { reverse: this.force ?? 'auto' });
        },
        get status() {
            const r = this.result;
            if (!this.$store.text.value.trim()) return 'empty';
            if (this.force !== null) return r.reversedApplied ? 'manual-reversed' : 'manual-kept';
            if (r.reversedApplied) return 'auto-reversed';
            return r.detection.reversed === null ? 'unsure' : 'logical';
        },
        toggleReverse() {
            this.force = !this.result.reversedApplied;
        },
    }));

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
