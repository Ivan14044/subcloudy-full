<template>
    <div class="layout-wrapper">
        <AnimatedBackdrop :is-dark="isDark" />
        <Header v-if="!route.meta.requiresGuest" />
        <main class="main-content">
            <router-view v-if="!isLoading" />
        </main>
        <Footer v-if="!route.meta.requiresGuest" />
        <ScrollToTop />
        <CookieBanner />
        <SupportModal ref="supportModalRef" />
        <SupportButton v-if="!route.meta.requiresGuest" />
    </div>
</template>

<script setup lang="ts">
import { defineProps, ref, provide } from 'vue';
import { useRoute } from 'vue-router';
import { useTheme } from '@/composables/useTheme';

import Header from '@/components/layout/MainHeader.vue';
import Footer from '@/components/layout/MainFooter.vue';
import CookieBanner from '@/components/CookieBanner.vue';
import ScrollToTop from '@/components/ScrollToTop.vue';
import AnimatedBackdrop from '@/components/layout/AnimatedBackdrop.vue';
import SupportButton from '@/components/SupportButton.vue';
import SupportModal from '@/components/SupportModal.vue';

defineProps<{
    isLoading: boolean;
}>();

const route = useRoute();
const { isDark } = useTheme();

// Создаем ref для SupportModal, чтобы получить доступ к методу open
const supportModalRef = ref<{ open: () => void } | null>(null);

// Функция для открытия модального окна поддержки
const openSupportModal = () => {
    if (supportModalRef.value) {
        supportModalRef.value.open();
    } else {
        // Fallback: используем событие window
        window.dispatchEvent(new CustomEvent('open-support-modal'));
    }
};

// Предоставляем функцию через provide для SupportButton
provide('openSupportModal', openSupportModal);
</script>

<style scoped>
.layout-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background-color: rgb(243, 244, 246); /* bg-gray-100 */
}

.dark .layout-wrapper {
    background-color: rgb(17, 24, 39); /* dark:bg-gray-900 */
}

/* Убеждаемся, что main контент находится поверх AnimatedBackdrop */
.main-content {
    flex: 1 0 auto;
    position: relative;
    z-index: 1;
    opacity: 1 !important;
    filter: none !important;
}
</style>
