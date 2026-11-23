import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from './layouts/AppLayout.vue';

const pages = import.meta.glob('./pages/**/*.vue');

createInertiaApp({
    title: (title) => `${title} · MangaWeb`,
    resolve: async (name) => {
        const loader = pages[`./pages/${name}.vue`];
        if (!loader) throw new Error(`Página Vue no encontrada: ${name}`);
        const module = await loader() as { default: { layout?: unknown } };
        const page = module.default;
        if (name !== 'Login' && name !== 'Register') page.layout = AppLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
    progress: { color: '#a78bfa', delay: 150 },
});
