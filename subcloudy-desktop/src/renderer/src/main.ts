import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import i18n from './i18n';
import axios from 'axios';
import './style.css';

// Настройка Axios
axios.defaults.baseURL = 'https://subcloudy.com/api';
axios.defaults.withCredentials = true;

// Перехватчик для добавления токена авторизации
axios.interceptors.request.use(async (config) => {
  try {
    const token = await window.electronAPI.auth.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Добавляем локаль
    const currentLocale = i18n?.global?.locale?.value ?? i18n?.global?.locale;
    if (currentLocale) {
      config.headers['X-Locale'] = currentLocale;
    }
  } catch (error) {
    console.error('[Axios] Request interceptor error:', error);
  }
  return config;
});

// Глобальный обработчик ошибок
window.addEventListener('error', (event) => {
  console.error('[Global] Error:', event.error);
});

window.addEventListener('unhandledrejection', (event) => {
  console.error('[Global] Unhandled rejection:', event.reason);
});

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(i18n);

app.mount('#app');


