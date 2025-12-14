import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ru from './locales/ru.json';
import uk from './locales/uk.json';

const getBrowserLocale = () => {
    const browserLang = navigator.language || 'ru';
    const languageCode = browserLang.split('-')[0];
    const supportedLanguages = ['en', 'ru', 'uk'];
    return supportedLanguages.includes(languageCode) ? languageCode : 'ru';
};

const savedLanguage = localStorage.getItem('user-language');

const i18n = createI18n({
    legacy: false,
    globalInjection: true,
    locale: savedLanguage ?? 'ru', // Русский по умолчанию
    fallbackLocale: 'en',
    messages: {
        en,
        ru,
        uk
    },
    // Отключаем linked format полностью
    missingWarn: false,
    fallbackWarn: false,
    // Отключаем linked format синтаксис
    modifiers: {},
    // Используем простой формат без linked
    formatFallbackMessages: true
});

export default i18n;


