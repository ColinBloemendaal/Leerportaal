// WCAG 2.x contrast ratio (relative luminance formula) --
// https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html
// AA thresholds: 4.5:1 for normal text, 3:1 for large text/UI components.

function hexToRgb(hex: string): [number, number, number] | null {
    const normalized = hex.replace('#', '');
    const full =
        normalized.length === 3
            ? normalized
                  .split('')
                  .map((c) => c + c)
                  .join('')
            : normalized;

    if (!/^[0-9a-fA-F]{6}$/.test(full)) {
        return null;
    }

    return [parseInt(full.slice(0, 2), 16), parseInt(full.slice(2, 4), 16), parseInt(full.slice(4, 6), 16)];
}

function relativeLuminance([r, g, b]: [number, number, number]): number {
    const channel = (c: number) => {
        const s = c / 255;
        return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
    };

    return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
}

/**
 * Returns null if either color isn't a valid hex color (caller decides
 * how to handle "can't tell yet", e.g. while the user is still typing).
 */
export function contrastRatio(hexA: string, hexB: string): number | null {
    const rgbA = hexToRgb(hexA);
    const rgbB = hexToRgb(hexB);

    if (rgbA === null || rgbB === null) {
        return null;
    }

    const lA = relativeLuminance(rgbA);
    const lB = relativeLuminance(rgbB);
    const lighter = Math.max(lA, lB);
    const darker = Math.min(lA, lB);

    return (lighter + 0.05) / (darker + 0.05);
}

export function meetsWcagAA(hexA: string, hexB: string, largeText = false): boolean | null {
    const ratio = contrastRatio(hexA, hexB);

    if (ratio === null) {
        return null;
    }

    return ratio >= (largeText ? 3 : 4.5);
}
