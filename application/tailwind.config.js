import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

const buildPalette = (token, stops, defaultStop) => {
    const palette = stops.reduce((acc, stop) => {
        acc[stop] = `rgb(var(--icas-${token}-${stop}) / <alpha-value>)`;
        return acc;
    }, {});
    if (defaultStop && palette[defaultStop]) {
        palette.DEFAULT = palette[defaultStop];
    }
    return palette;
};

const primaryStops = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'];
const surfaceStops = ['0', '50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'];

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class', '[data-theme="dark"]'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: buildPalette('primary', primaryStops, '500'),
                'primary-contrast': `rgb(var(--icas-primary-contrast) / <alpha-value>)`,
                surface: buildPalette('surface', surfaceStops, '200'),
                'surface-contrast': `rgb(var(--icas-surface-contrast) / <alpha-value>)`,
            },
        },
    },

    plugins: [forms, typography],
};
