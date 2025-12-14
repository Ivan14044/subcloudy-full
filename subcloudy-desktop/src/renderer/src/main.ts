import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import i18n from './i18n';
import './style.css';

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


