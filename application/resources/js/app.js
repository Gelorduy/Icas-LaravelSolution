import './bootstrap';
import '../css/app.css';
import 'primeicons/primeicons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import StyleClass from 'primevue/styleclass';
import { bootstrapPalettes } from './utils/themePalettes';
import { bootstrapThemeMode } from './utils/themeMode';

const appName = import.meta.env.VITE_APP_NAME || 'ICAS';

bootstrapThemeMode();
bootstrapPalettes();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin);
        app.use(pinia);
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: '.dark',
                    cssLayer: false
                }
            }
        });
        app.directive('styleclass', StyleClass);
        app.use(ZiggyVue);
        
        return app.mount(el);
    },
    progress: {
        color: '#3b82f6',
    },
});
