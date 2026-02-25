// Load Bootstrap CSS first, then custom app styles
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'flatpickr/dist/flatpickr.min.css';
import '../css/app.css';
import '../css/wow-buttons.css';
import '../css/wow-cards.css';
import '../css/site.css';
import '../css/home-hero.css';
// Load Bootstrap JS (Popper included via dependency)
import 'bootstrap';
import './bootstrap';
// Header + homepage interactivity (mega menu, mobile drawer, search panes)
import './home';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initDrawRandomUnderline } from './lib/wow-links';
import { initClickLoaders } from './lib/wow-buttons';
import './lib/cart-shortcuts';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    // If a page passes a full title that already contains the app name,
    // don't append it again. Otherwise, append using a hyphen separator.
    title: (title) => {
        const t = String(title || '').trim();
        return t && t.includes(appName) ? t : `${t} - ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vue = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        // Init link underline + button loaders on first mount
        try { initDrawRandomUnderline(); initClickLoaders(); } catch {}

        // Re-init after each successful Inertia navigation
        try {
            document.addEventListener('inertia:success', () => {
                initDrawRandomUnderline();
                initClickLoaders();
            });
        } catch {}

        return vue;
    },
    progress: {
        color: '#549483',
    },
});
