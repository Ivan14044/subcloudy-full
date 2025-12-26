<template>
    <component :is="layoutComponent" :is-loading="isLoading" />
    <FullPageLoader :overlay="!isLoading" @call-hide-loader="hideLoader" />
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

import DefaultLayout from '@/components/layout/DefaultLayout.vue';
import EmptyLayout from '@/components/layout/EmptyLayout.vue';
import FullPageLoader from '@/components/FullPageLoader.vue';

import { useServiceStore } from '@/stores/services';
import { usePageStore } from '@/stores/pages';
import { useOptionStore } from '@/stores/options';
import { useNotificationStore } from '@/stores/notifications';
import { useLoadingStore } from '@/stores/loading';
import { useAuthStore } from '@/stores/auth';
import { useSupportStore } from '@/stores/support';

import logo from '@/assets/logo.webp';
import { prefetchCriticalRoutes } from '@/utils/prefetchUtils';

const { locale } = useI18n();
const route = useRoute();
const isLoading = ref(true);
const loadingStore = useLoadingStore();
const authStore = useAuthStore();

const isStartSessionPage = /^\/session-start(\/\d+)?$/.test(window.location.pathname);

const layoutComponent = computed(() => (isStartSessionPage ? EmptyLayout : DefaultLayout));

// Force scroll to top on route change
watch(() => route.path, () => {
    window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'instant' as any
    });
});

onMounted(async () => {
    loadingStore.start();
    window.addEventListener('app:hide-loader', hideLoader);

    if (isStartSessionPage) {
        return;
    }

    authStore.init();

    const pageStore = usePageStore();
    const serviceStore = useServiceStore();
    const optionStore = useOptionStore();
    const notificationStore = useNotificationStore();
    const supportStore = useSupportStore();

    // Используем Promise.allSettled для устойчивости к ошибкам
    const promises = [
        pageStore.fetchData(locale.value),
        serviceStore.fetchData(),
        optionStore.fetchData(),
        // Загружаем уведомления только если пользователь авторизован
        authStore.user ? notificationStore.fetchData() : Promise.resolve(),
        // Инициализируем техподдержку в фоне (для уведомлений)
        (localStorage.getItem('support_ticket_id') || authStore.isAuthenticated) 
            ? supportStore.ensureTicket().then(ok => {
                if (ok) {
                    const pollEmail = authStore.isAuthenticated ? undefined : (supportStore.guestEmail || localStorage.getItem('support_guest_email') || undefined);
                    supportStore.startPolling(pollEmail);
                }
            }) 
            : Promise.resolve()
    ];

    await Promise.allSettled(promises);

    preloadImages([logo, `/img/lang/${locale.value}.png`]);

    // Prefetch критических маршрутов после загрузки
    prefetchCriticalRoutes();

    loadingStore.stop();
    isLoading.value = false;
});

onUnmounted(() => {
    window.removeEventListener('app:hide-loader', hideLoader);
});

const preloadImages = (urls: string[]) => {
    urls.forEach(url => {
        const img = new Image();
        img.src = url;
    });
};

function hideLoader() {
    loadingStore.stop();
    isLoading.value = false;
}
</script>
