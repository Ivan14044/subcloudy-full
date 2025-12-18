import axios from 'axios';
import i18n from './i18n';

// Всегда используем относительный путь /api
// В dev режиме Vite proxy перенаправляет на Laravel сервер
// В production фронтенд и бэкенд на одном домене, поэтому относительный путь работает
const apiBase = import.meta.env.VITE_API_BASE || '/api';

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
