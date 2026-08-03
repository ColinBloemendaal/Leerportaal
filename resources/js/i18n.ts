import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import nl from './locales/nl.json';

export function createAppI18n(locale: string) {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'nl',
        messages: { en, nl },
    });
}
