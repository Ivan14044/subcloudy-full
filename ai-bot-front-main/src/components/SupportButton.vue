<template>
    <button
        v-if="isVisible"
        class="support-button fixed bottom-6 right-6 z-[9998] w-14 h-14 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 cursor-pointer"
        :aria-label="t('support.button')"
        @click="handleClick"
        @mousedown.stop
        @mouseup.stop
        type="button"
    >
        <div v-if="supportStore.unreadCount > 0" class="unread-badge">
            {{ supportStore.unreadCount }}
        </div>
        <svg
            class="w-6 h-6"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.316l-2.295 2.295a.75.75 0 01-1.06-1.06l2.295-2.296A9.764 9.764 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
            />
        </svg>
    </button>
</template>

<script setup lang="ts">
import { inject, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSupportStore } from '@/stores/support';

const { t } = useI18n();
const supportStore = useSupportStore();

// Получаем функцию открытия модального окна через provide/inject
const openModal = inject<() => void>('openSupportModal');

const isVisible = computed(() => !supportStore.isModalVisible);

const handleClick = (event: MouseEvent) => {
    // Предотвращаем всплытие события
    event.stopPropagation();
    event.preventDefault();
    
    // Открываем модальное окно
    if (openModal) {
        openModal();
    } else {
        // Fallback: используем событие window, если provide не работает
        console.warn('openSupportModal не найден через provide/inject, используем событие window');
        window.dispatchEvent(new CustomEvent('open-support-modal'));
    }
};

// Проверяем, что функция доступна при монтировании
onMounted(() => {
    if (!openModal) {
        console.warn('SupportButton: openSupportModal не предоставлен через provide');
    }
});
</script>

<style scoped>
.support-button {
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3), 0 8px 10px -6px rgba(99, 102, 241, 0.3);
    pointer-events: auto !important;
    position: fixed !important;
    z-index: 9998 !important;
}

.support-button:hover {
    box-shadow: 0 15px 35px -5px rgba(99, 102, 241, 0.4), 0 10px 15px -6px rgba(99, 102, 241, 0.4);
}

.support-button:active {
    transform: scale(0.95);
}

.unread-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.support-button * {
    pointer-events: none;
}

@media (max-width: 640px) {
    .support-button {
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
    }
}
</style>
