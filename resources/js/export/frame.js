// Output geometry shared by the PNG (free) and SVG (Pro) exporters. No dependencies.

/**
 * Geometry shared by the PNG (free) and SVG (Pro) outputs.
 * Returns the box in font units and each line's origin.
 */
export function frame(result, { align = 'right', padding = 0.25 } = {}) {
    const pad = result.upem * padding;
    const width = result.width + 2 * pad;
    const height = result.lines.length * result.lineHeight + 2 * pad;
    const top = pad + (result.lineHeight - (result.ascender - result.descender)) / 2 + result.ascender;
    const origins = result.lines.map((line, i) => ({
        x: pad + (align === 'left' ? 0 : align === 'center' ? (result.width - line.width) / 2 : result.width - line.width),
        y: top + i * result.lineHeight,
    }));
    return { width, height, origins };
}

