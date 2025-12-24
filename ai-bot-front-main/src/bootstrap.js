import axios from 'axios';
import i18n from './i18n';

// Всегда используем относительный путь /api
// В dev режиме Vite proxy перенаправляет на Laravel сервер
// В production фронтенд и бэкенд на одном домене, поэтому относительный путь работает
// Определяем режим работы: в production используем относительный путь, в dev - через proxy
const isProduction = import.meta.env.MODE === 'production' || import.meta.env.PROD;

// В продакшене всегда используем относительный путь /api
// Игнорируем VITE_API_BASE если он содержит dev URL
let apiBase = import.meta.env.VITE_API_BASE || '/api';
if (isProduction) {
    // В продакшене игнорируем dev URL и всегда используем относительный путь
    if (apiBase.includes('127.0.0.1') || apiBase.includes('localhost')) {
        apiBase = '/api';
    }
    // Убеждаемся, что это относительный путь
    if (apiBase.startsWith('http://') || apiBase.startsWith('https://')) {
        apiBase = '/api';
    }
}

// Логируем для отладки в dev режиме
if (!isProduction) {
    console.log('[Bootstrap] API Base URL:', apiBase, 'Mode:', import.meta.env.MODE, 'PROD:', import.meta.env.PROD);
} else {
    // В продакшене логируем только для отладки
    console.log('[Bootstrap] Production mode - API Base URL:', apiBase);
}

axios.defaults.baseURL = apiBase;
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use((config) => {
    const currentLocale = i18n?.global?.locale?.value ?? i18n?.global?.locale;
    if (currentLocale) {
        const headers = config.headers;
        if (headers && typeof headers.set === 'function') {
            if (!headers.has('X-Locale')) {
                headers.set('X-Locale', currentLocale);
            }
        } else {
            config.headers = {
                ...(headers || {}),
                'X-Locale': headers?.['X-Locale'] ?? currentLocale
            };
        }
    }
    return config;
});

export default axios;
