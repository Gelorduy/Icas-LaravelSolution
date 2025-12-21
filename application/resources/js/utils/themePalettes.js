const PRIMARY_PALETTES = [
    {
        key: 'blue',
        label: 'Command Blue',
        shortLabel: 'Blue',
        preview: ['#1d4ed8', '#60a5fa'],
        contrast: '#ffffff',
        shades: {
            50: '#eff6ff',
            100: '#dbeafe',
            200: '#bfdbfe',
            300: '#93c5fd',
            400: '#60a5fa',
            500: '#3b82f6',
            600: '#2563eb',
            700: '#1d4ed8',
            800: '#1e40af',
            900: '#1e3a8a',
            950: '#172554',
        },
    },
    {
        key: 'emerald',
        label: 'Ops Emerald',
        shortLabel: 'Emerald',
        preview: ['#047857', '#34d399'],
        contrast: '#ffffff',
        shades: {
            50: '#ecfdf5',
            100: '#d1fae5',
            200: '#a7f3d0',
            300: '#6ee7b7',
            400: '#34d399',
            500: '#10b981',
            600: '#059669',
            700: '#047857',
            800: '#065f46',
            900: '#064e3b',
            950: '#022c22',
        },
    },
    {
        key: 'amber',
        label: 'Amber Alert',
        shortLabel: 'Amber',
        preview: ['#b45309', '#fbbf24'],
        contrast: '#1c1917',
        shades: {
            50: '#fffbeb',
            100: '#fef3c7',
            200: '#fde68a',
            300: '#fcd34d',
            400: '#fbbf24',
            500: '#f59e0b',
            600: '#d97706',
            700: '#b45309',
            800: '#92400e',
            900: '#78350f',
            950: '#451a03',
        },
    },
    {
        key: 'violet',
        label: 'Violet Ops',
        shortLabel: 'Violet',
        preview: ['#5b21b6', '#a78bfa'],
        contrast: '#f8f5ff',
        shades: {
            50: '#f5f3ff',
            100: '#ede9fe',
            200: '#ddd6fe',
            300: '#c4b5fd',
            400: '#a78bfa',
            500: '#8b5cf6',
            600: '#7c3aed',
            700: '#6d28d9',
            800: '#5b21b6',
            900: '#4c1d95',
            950: '#2e1065',
        },
    },
    {
        key: 'rose',
        label: 'Signal Rose',
        shortLabel: 'Rose',
        preview: ['#be123c', '#fb7185'],
        contrast: '#fff1f2',
        shades: {
            50: '#fff1f2',
            100: '#ffe4e6',
            200: '#fecdd3',
            300: '#fda4af',
            400: '#fb7185',
            500: '#f43f5e',
            600: '#e11d48',
            700: '#be123c',
            800: '#9f1239',
            900: '#881337',
            950: '#4c0519',
        },
    },
];

const SURFACE_PALETTES = [
    {
        key: 'slate',
        label: 'Slate Mist',
        shortLabel: 'Slate',
        preview: ['#f8fafc', '#1e293b'],
        contrast: '#0f172a',
        shades: {
            0: '#ffffff',
            50: '#f8fafc',
            100: '#f1f5f9',
            200: '#e2e8f0',
            300: '#cbd5e1',
            400: '#94a3b8',
            500: '#64748b',
            600: '#475569',
            700: '#334155',
            800: '#1e293b',
            900: '#0f172a',
            950: '#020617',
        },
    },
    {
        key: 'zinc',
        label: 'Industrial Zinc',
        shortLabel: 'Zinc',
        preview: ['#f4f4f5', '#27272a'],
        contrast: '#18181b',
        shades: {
            0: '#ffffff',
            50: '#fafafa',
            100: '#f4f4f5',
            200: '#e4e4e7',
            300: '#d4d4d8',
            400: '#a1a1aa',
            500: '#71717a',
            600: '#52525b',
            700: '#3f3f46',
            800: '#27272a',
            900: '#18181b',
            950: '#09090b',
        },
    },
    {
        key: 'stone',
        label: 'Warm Stone',
        shortLabel: 'Stone',
        preview: ['#f5f5f4', '#292524'],
        contrast: '#1c1917',
        shades: {
            0: '#ffffff',
            50: '#fafaf9',
            100: '#f5f5f4',
            200: '#e7e5e4',
            300: '#d6d3d1',
            400: '#a8a29e',
            500: '#78716c',
            600: '#57534e',
            700: '#44403c',
            800: '#292524',
            900: '#1c1917',
            950: '#0c0a09',
        },
    },
];

const STORAGE_KEYS = {
    primary: 'icas-primary-palette',
    surface: 'icas-surface-palette',
};

const paletteLookup = {
    primary: PRIMARY_PALETTES,
    surface: SURFACE_PALETTES,
};

const hexToRgbChannels = (hex) => {
    const normalized = hex.replace('#', '');
    const value = normalized.length === 3
        ? normalized.split('').map((char) => char + char).join('')
        : normalized;
    const r = parseInt(value.substring(0, 2), 16);
    const g = parseInt(value.substring(2, 4), 16);
    const b = parseInt(value.substring(4, 6), 16);
    return `${r} ${g} ${b}`;
};

const setCssVariable = (variable, hex) => {
    if (typeof document === 'undefined') {
        return;
    }
    document.documentElement.style.setProperty(variable, hexToRgbChannels(hex));
};

export const applyPalette = (type, paletteKey, persist = true) => {
    const pool = paletteLookup[type];
    if (!pool || pool.length === 0) {
        return null;
    }

    const palette = pool.find((item) => item.key === paletteKey) ?? pool[0];

    Object.entries(palette.shades).forEach(([shade, hex]) => {
        setCssVariable(`--icas-${type}-${shade}`, hex);
    });

    if (palette.contrast) {
        setCssVariable(`--icas-${type}-contrast`, palette.contrast);
    }

    if (persist && typeof window !== 'undefined') {
        window.localStorage?.setItem(STORAGE_KEYS[type], palette.key);
    }

    return palette.key;
};

export const bootstrapPalettes = () => {
    if (typeof window === 'undefined') {
        return;
    }

    const primarySelection = window.localStorage?.getItem(STORAGE_KEYS.primary) ?? PRIMARY_PALETTES[0].key;
    const surfaceSelection = window.localStorage?.getItem(STORAGE_KEYS.surface) ?? SURFACE_PALETTES[0].key;

    applyPalette('primary', primarySelection, false);
    applyPalette('surface', surfaceSelection, false);
};

export { PRIMARY_PALETTES, SURFACE_PALETTES, STORAGE_KEYS };
