<template>
    <button
        class="scroll-to-top-btn glass-button text-dark dark:text-white/90 fixed bottom-6 right-6 z-50 rounded-full w-10 h-10 flex items-center justify-center transition-none"
        :class="{ visible: isVisible, 'glass-dark': isDark, 'glass-light': !isDark }"
        aria-label="Scroll to top"
        @click="scrollToTop"
    >
        <svg
            class="w-5 h-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M5 15l7-7 7 7"
            />
        </svg>
    </button>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useTheme } from '@/composables/useTheme';

const { isDark } = useTheme();
const isVisible = ref(false);

const handleScroll = () => {
    isVisible.value = window.scrollY > 300;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
/* Стиль кнопки "Scroll to top" - минималистичный, как на карточках сервисов */
.scroll-to-top-btn {
    /* Базовый стиль уже применен через glass-button */
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.scroll-to-top-btn.visible {
    opacity: 1;
    pointer-events: auto;
}

.scroll-to-top-btn:hover {
    opacity: 0.8;
}

.scroll-to-top-btn:active {
    opacity: 0.7;
}

/* Дополнительные стили для светлой темы */
.scroll-to-top-btn.glass-light {
    /* Стиль уже определен в app.css через .glass-button */
}

/* Дополнительные стили для темной темы */
.scroll-to-top-btn.glass-dark {
    background: rgba(12, 24, 60, 0.24);
    border-color: rgba(255, 255, 255, 0.06);
}
</style>
