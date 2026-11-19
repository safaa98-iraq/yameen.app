// Export page and batch page components. Heavy code (HarfBuzz wasm, fonts,
// Pro modules) loads only when the user exports or processes a file.
import { loadFont as loadPreviewFont, nearestWeight } from './lib/font-loader';

const proUrl = (name) => `${document.querySelector('meta[name="yameen-pro"]')?.content ?? '/pro'}/${name}.js`;

function download(blob, filename) {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
}

export function registerExporter(Alpine) {
    Alpine.data('exporter', (config) => ({
        fonts: config.fonts,
        font: config.font ?? config.fonts[0]?.slug,
        weight: 400,
        color: '#1E1A2B',
        transparent: false,
        width: 1000,
        align: 'right',
        busy: false,
        error: '',
        init() {
            this.$watch('font', () => this.loadPreview());
            this.$watch('weight', () => this.loadPreview());
            this.loadPreview();
        },
        get selected() {
            return this.fonts.find((f) => f.slug === this.font) ?? this.fonts[0];
        },
        get usedWeight() {
            return nearestWeight(Object.keys(this.selected?.ttf ?? {}).map(Number), this.weight);
        },
        get maxWidth() {
            return this.$store.credits.pro ? 4000 : 1000;
        },
        get previewStyle() {
            const f = this.selected;
            return {
                fontFamily: f ? `'${f.family}', var(--font-ar)` : 'var(--font-ar)',
                fontWeight: this.usedWeight,
                color: this.color,
                textAlign: this.align === 'right' ? 'right' : this.align === 'left' ? 'left' : 'center',
            };
        },
        loadPreview() {
            if (this.selected) loadPreviewFont(this.selected, this.usedWeight);
        },
        async layout() {
            const text = this.$store.text.value.replace(/\s+$/, '');
            if (!text.trim()) {
                this.error = this.$el.dataset.emptyMessage;
                return null;
            }
            this.error = '';
            const mod = await import('./export/layout');
            const font = await mod.loadFont(this.selected.ttf[this.usedWeight]);
            return { mod, result: mod.layout(text, font) };
        },
        async png() {
            if (this.busy) return;
            this.busy = true;
            try {
                if (!(await this.$store.credits.consume('png', this.$store.text.value))) return;
                const out = await this.layout();
                if (!out) return;
                const widthPx = Math.max(100, Math.min(Number(this.width) || 1000, this.maxWidth));
                const background = this.transparent && this.$store.credits.pro ? null : '#FFFFFF';
                const canvas = out.mod.drawToCanvas(out.result, { widthPx, color: this.color, background, align: this.align });
                canvas.toBlob((blob) => blob && download(blob, 'yameen.png'), 'image/png');
            } catch {
                this.error = this.$el.dataset.failMessage;
            } finally {
                this.busy = false;
            }
        },
        async svg() {
            if (this.busy) return;
            this.busy = true;
            try {
                if (!(await this.$store.credits.consume('svg'))) return;
                const [{ toSvg }, out] = await Promise.all([import(/* @vite-ignore */ proUrl('svg')), this.layout()]);
                if (!out) return;
                const svg = toSvg(out.result, {
                    color: this.color,
                    background: this.transparent ? null : '#FFFFFF',
                    align: this.align,
                    title: this.$store.text.value.slice(0, 120),
                });
                download(new Blob([svg], { type: 'image/svg+xml' }), 'yameen.svg');
            } catch {
                this.error = this.$el.dataset.failMessage;
            } finally {
                this.busy = false;
            }
        },
    }));

    Alpine.data('batchTool', () => ({
        mod: null,
        file: null,
        type: null,
        text: '',
        fields: [],
        chosen: [],
        options: { stripTashkeel: true, shape: true, reverse: true },
        error: '',
        done: false,
        async choose(event) {
            this.error = '';
            this.done = false;
            const file = event.target.files?.[0];
            if (!file) return;
            try {
                this.mod ??= await import(/* @vite-ignore */ proUrl('batch'));
            } catch {
                this.error = this.$el.dataset.proMessage;
                return;
            }
            const type = this.mod.detectType(file.name);
            if (!type) return (this.error = this.$el.dataset.typeMessage);
            if (file.size > this.mod.MAX_BYTES) return (this.error = this.$el.dataset.sizeMessage);
            this.text = await file.text();
            try {
                this.fields = this.mod.inspect(type, this.text).fields;
            } catch {
                return (this.error = this.$el.dataset.parseMessage);
            }
            this.file = file;
            this.type = type;
            this.chosen = [...this.fields];
        },
        async run() {
            if (!this.file || !(await this.$store.credits.consume('batch'))) return;
            try {
                const out = this.mod.process(this.type, this.text, { fields: this.chosen, options: this.options });
                const name = this.file.name.replace(/(\.[^.]+)$/, '.yameen$1');
                download(new Blob([out], { type: this.file.type || 'text/plain' }), name);
                this.done = true;
            } catch {
                this.error = this.$el.dataset.parseMessage;
            }
        },
    }));
}
