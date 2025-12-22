<template>
    <button
        @click="openSupport"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110"
        :aria-label="$t('support.button')"
    >
        <MessageCircle class="w-6 h-6" />
    </button>
</template>

<script setup lang="ts">
import { MessageCircle } from 'lucide-vue-next';
import { useSupportStore } from '@/stores/support';
import { useAuthStore } from '@/stores/auth';

const supportStore = useSupportStore();
const authStore = useAuthStore();

const openSupport = () => {
    // Эмитируем событие для открытия модального окна поддержки
    window.dispatchEvent(new CustomEvent('open-support-modal'));
};
</script>

