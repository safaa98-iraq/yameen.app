// Alpine components for font previews.
import { loadFont, nearestWeight, whenVisible } from './lib/font-loader';
import { copyText } from './lib/clipboard';

function fontStyle(font, weight, size) {
    const style = { fontFamily: `'${font.family}', var(--font-ar)`, fontWeight: nearestWeight(font.weights, weight) };
    if (size) style.fontSize = `${size}px`;
    return style;
}

export function registerFonts(Alpine) {
    // Grid with filters. Cards read shared settings from here.
    Alpine.data('fontGrid', (config = {}) => ({
        query: '',
        style: config.style ?? '',
        apps: false,
        diacritics: false,
        size: 40,
        weight: 400,
        sample: config.sample ?? '',
        get text() {
            return this.$store.text.value.trim() || this.sample;
        },
        matches(font) {
            if (this.style && font.style !== this.style) return false;
            if (this.apps && !font.apps) return false;
            if (this.diacritics && !font.diacritics) return false;
            const q = this.query.trim().toLowerCase();
            return !q || font.name.toLowerCase().includes(q) || font.slug.includes(q);
        },
        get visibleCount() {
            return (config.fonts ?? []).filter((f) => this.matches(f)).length;
        },
    }));

    Alpine.data('fontCard', (font) => ({
        font,
        ready: false,
        copied: false,
        init() {
            whenVisible(this.$el, () => {
                this.ready = true;
                loadFont(font, this.weight);
            });
            this.$watch('weight', (w) => this.ready && loadFont(font, w));
        },
        get specimenStyle() {
            // Until the font is in view, keep the UI font so the card has its final height.
            return this.ready ? fontStyle(font, this.weight, this.size) : { fontSize: `${this.size}px` };
        },
        get usedWeight() {
            return nearestWeight(font.weights, this.weight);
        },
        async copyCss(css) {
            if (await copyText(css)) {
                this.copied = true;
                setTimeout(() => (this.copied = false), 1500);
            }
        },
    }));

    // Single font page: specimen + one line per weight.
    Alpine.data('fontSpecimen', (font, sample) => ({
        font,
        sample,
        size: 72,
        init() {
            font.weights.forEach((w) => loadFont(font, w));
        },
        get text() {
            return this.$store.text.value.trim() || this.sample;
        },
        styleFor(weight, size = null) {
            return fontStyle(font, weight, size);
        },
    }));

    Alpine.data('fontCompare', (fonts, a, b, sample) => ({
        fonts,
        a,
        b,
        size: 48,
        weight: 400,
        sample,
        init() {
            const params = new URLSearchParams(window.location.search);
            for (const key of ['a', 'b']) {
                const slug = params.get(key);
                if (slug && this.byslug(slug)) this[key] = slug;
            }
            this.load();
            this.$watch('a', () => this.load());
            this.$watch('b', () => this.load());
            this.$watch('weight', () => this.load());
        },
        byslug(slug) {
            return this.fonts.find((f) => f.slug === slug);
        },
        load() {
            loadFont(this.byslug(this.a), this.weight);
            loadFont(this.byslug(this.b), this.weight);
            const url = new URL(window.location.href);
            url.searchParams.set('a', this.a);
            url.searchParams.set('b', this.b);
            history.replaceState(null, '', url);
        },
        get text() {
            return this.$store.text.value.trim() || this.sample;
        },
        styleFor(slug) {
            const f = this.byslug(slug);
            return f ? fontStyle(f, this.weight, this.size) : {};
        },
    }));

    // Lazy-load any font a static element needs: <div x-data x-font="{...font}" data-weight="700">
    Alpine.directive('font', (el, { expression }, { evaluate }) => {
        const font = evaluate(expression);
        const weight = Number(el.dataset.weight ?? 400);
        whenVisible(el, () => loadFont(font, weight));
    });
}

export { loadFont };
