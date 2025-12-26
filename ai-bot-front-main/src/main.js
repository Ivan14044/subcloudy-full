import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import i18n from './i18n';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import './assets/app.css';
import IntersectDirective from './directives/intersect';

// Disable native scroll restoration to let Vue Router handle it
if ('scrollRestoration' in window.history) {
    window.history.scrollRestoration = 'manual';
}

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

const app = createApp(App);
app.use(i18n);
app.use(router);
app.use(pinia);

// Асинхронная загрузка Toast и его CSS для уменьшения начального бандла
import('vue-toastification').then(({ default: Toast, POSITION }) => {
    import('vue-toastification/dist/index.css');
    app.use(Toast, {
        position: POSITION.TOP_RIGHT,
        timeout: 5000
    });
});

app.directive('intersect', IntersectDirective);
app.mount('#app');
