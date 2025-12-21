const MODE_STORAGE_KEY = 'icas-theme-mode';
const DEFAULT_MODE = 'dark';

export const setThemeMode = (mode, persist = true) => {
    if (typeof document === 'undefined') {
        return DEFAULT_MODE;
    }

    const nextMode = mode === 'light' ? 'light' : 'dark';
    document.documentElement.dataset.theme = nextMode;
    document.documentElement.classList.toggle('dark', nextMode === 'dark');

    if (persist && typeof window !== 'undefined') {
        window.localStorage?.setItem(MODE_STORAGE_KEY, nextMode);
    }

    return nextMode;
};

export const getThemeMode = () => {
    if (typeof document === 'undefined') {
        return DEFAULT_MODE;
    }
    const stored = typeof window !== 'undefined' ? window.localStorage?.getItem(MODE_STORAGE_KEY) : null;
    return document.documentElement.dataset.theme || stored || DEFAULT_MODE;
};

export const bootstrapThemeMode = () => {
    if (typeof document === 'undefined') {
        return;
    }
    const stored = typeof window !== 'undefined' ? window.localStorage?.getItem(MODE_STORAGE_KEY) : null;
    setThemeMode(stored || DEFAULT_MODE, false);
};

export const toggleThemeMode = () => {
    const current = getThemeMode();
    const next = current === 'dark' ? 'light' : 'dark';
    return setThemeMode(next);
};
