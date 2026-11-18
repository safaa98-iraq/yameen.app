// Load a font weight on demand with the FontFace API. Each weight loads once.
const pending = new Map();

export function nearestWeight(weights, target) {
    const list = weights?.length ? weights : [400];
    return [...list].sort((a, b) => Math.abs(a - target) - Math.abs(b - target) || a - b)[0];
}

/** @param font {{family: string, weights: number[], preview: Record<string, string>}} */
export function loadFont(font, weight = 400) {
    if (!font) return Promise.resolve(null);
    const w = nearestWeight(font.weights, Number(weight));
    const url = font.preview?.[w];
    const key = `${font.family}:${w}`;
    if (!url) return Promise.resolve(null);
    if (!pending.has(key)) {
        const face = new FontFace(font.family, `url(${url}) format('woff2')`, { weight: String(w), display: 'swap' });
        pending.set(
            key,
            face.load().then(
                (loaded) => {
                    document.fonts.add(loaded);
                    return loaded;
                },
                () => null,
            ),
        );
    }
    return pending.get(key);
}

/** Run `callback` once when `el` first comes near the viewport. */
export function whenVisible(el, callback, rootMargin = '200px') {
    if (!('IntersectionObserver' in window)) {
        callback();
        return () => {};
    }
    const io = new IntersectionObserver(
        (entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                io.disconnect();
                callback();
            }
        },
        { rootMargin },
    );
    io.observe(el);
    return () => io.disconnect();
}
