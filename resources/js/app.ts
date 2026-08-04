import { createInertiaApp } from '@inertiajs/vue3';
import 'bootstrap';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import CookieConsentBanner from './Components/CookieConsentBanner.vue';
import { createAppI18n } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Leerportaal';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Locale is decided server-side (config('app.locale')) and shared
        // via Inertia -- there's no runtime language switcher, so it only
        // needs to be read once at boot, not kept in sync afterwards.
        const initialProps = props.initialPage.props as { locale?: string };
        const locale = initialProps.locale ?? 'nl';

        createApp({ render: () => [h(App, props), h(CookieConsentBanner)] })
            .use(plugin)
            .use(createAppI18n(locale))
            .mount(el);
    },
});
