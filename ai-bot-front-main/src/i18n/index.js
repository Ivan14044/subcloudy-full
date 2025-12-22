import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import uk from './locales/uk.json';
import ru from './locales/ru.json';
import zh from './locales/zh.json';
import es from './locales/es.json';

const getBrowserLocale = () => {
    const browserLang = navigator.language || navigator.userLanguage;

    const languageCode = browserLang.split('-')[0];

    const supportedLanguages = ['en', 'uk', 'ru', 'zh', 'es'];

    // Язык по умолчанию - русский (вместо украинского)
    return supportedLanguages.includes(languageCode) ? languageCode : 'ru';
};

const savedLanguage = localStorage.getItem('user-language');

// Валидация и очистка сообщений перед созданием i18n
function validateMessages(messages) {
    const cleaned = {};
    for (const [locale, msgs] of Object.entries(messages)) {
        try {
            // Проверяем, что это валидный объект
            if (msgs && typeof msgs === 'object') {
                // Рекурсивно проверяем значения на наличие некорректных строк
                // Используем глубокое копирование для избежания проблем с Proxy
                const clean = JSON.parse(JSON.stringify(msgs, (key, value) => {
                    // Пропускаем функции и undefined
                    if (typeof value === 'function' || value === undefined) {
                        return null;
                    }
                    // Обрабатываем циклические ссылки
                    if (typeof value === 'object' && value !== null) {
                        try {
                            JSON.stringify(value);
                        } catch (e) {
                            return null;
                        }
                    }
                    return value;
                }));
                cleaned[locale] = clean;
            } else {
                console.warn(`[i18n] Invalid messages for locale ${locale}, using empty object`);
                cleaned[locale] = {};
            }
        } catch (e) {
            console.error(`[i18n] Error validating messages for locale ${locale}:`, e);
            console.error(`[i18n] Error details:`, e.message, e.stack);
            cleaned[locale] = {};
        }
    }
    return cleaned;
}

// Безопасное создание i18n с обработкой ошибок
let i18n;
try {
    const validatedMessages = validateMessages({
        en: en,
        uk: uk,
        ru: ru,
        zh: zh,
        es: es
    });

    i18n = createI18n({
        legacy: false,
        globalInjection: true,
        locale: savedLanguage ?? 'ru', // Русский язык по умолчанию
        fallbackLocale: 'en',
        warnHtmlMessage: false,
        messages: validatedMessages,
        silentTranslationWarn: true,
        silentFallbackWarn: true,
        // Добавляем обработку ошибок парсинга
        missingWarn: false,
        fallbackWarn: false
    });
} catch (e) {
    console.error('[i18n] Critical error creating i18n instance:', e);
    // Создаем минимальный экземпляр i18n для предотвращения полного краха
    i18n = createI18n({
        legacy: false,
        globalInjection: true,
        locale: 'ru',
        fallbackLocale: 'en',
        messages: { ru: {}, en: {} },
        silentTranslationWarn: true,
        silentFallbackWarn: true
    });
}

export default i18n;
