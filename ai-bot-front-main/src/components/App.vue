<template>
    <component :is="layoutComponent" :is-loading="isLoading" />
    <FullPageLoader :overlay="!isLoading" @call-hide-loader="hideLoader" />
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch, defineAsyncComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

// Асинхронная загрузка layout компонентов для уменьшения начального бандла
const DefaultLayout = defineAsyncComponent(() => import('@/components/layout/DefaultLayout.vue'));
const EmptyLayout = defineAsyncComponent(() => import('@/components/layout/EmptyLayout.vue'));
import FullPageLoader from '@/components/FullPageLoader.vue';

import { useLoadingStore } from '@/stores/loading';
import { useAuthStore } from '@/stores/auth';

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

    // Динамически импортируем stores только когда они нужны
    const { usePageStore } = await import('@/stores/pages');
    const { useServiceStore } = await import('@/stores/services');
    const { useOptionStore } = await import('@/stores/options');

    const pageStore = usePageStore();
    const serviceStore = useServiceStore();
    const optionStore = useOptionStore();

    // Загружаем только критичные данные для первой отрисовки
    const criticalPromises = [
        pageStore.fetchData(locale.value),
        serviceStore.fetchData(),
        optionStore.fetchData()
    ];

    await Promise.allSettled(criticalPromises);

    // Откладываем загрузку некритичных данных
    setTimeout(async () => {
        const { useNotificationStore } = await import('@/stores/notifications');
        const { useSupportStore } = await import('@/stores/support');
        
        const notificationStore = useNotificationStore();
        const supportStore = useSupportStore();
        
        const nonCriticalPromises = [
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
        
        await Promise.allSettled(nonCriticalPromises);
    }, 100);

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
