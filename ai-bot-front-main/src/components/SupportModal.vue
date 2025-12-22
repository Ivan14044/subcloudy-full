<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
                @click.self="closeModal"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="isOpen"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $t('support.title') }}
                            </h2>
                            <button
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto p-4">
                            <!-- Выбор канала (если еще не выбран) -->
                            <div v-if="!channelSelected && !supportStore.hasTicket" class="space-y-4">
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    {{ $t('support.selectChannel') }}
                                </p>
                                
                                <button
                                    @click="selectChannel('web')"
                                    class="w-full p-4 border-2 border-indigo-500 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <MessageSquare class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $t('support.channelWeb') }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $t('support.channelWebDesc') }}
                                            </p>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    @click="selectChannel('telegram')"
                                    class="w-full p-4 border-2 border-indigo-500 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <Send class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $t('support.channelTelegram') }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $t('support.channelTelegramDesc') }}
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <!-- Форма email для гостей -->
                            <div v-if="!authStore.isAuthenticated && channelSelected === 'web' && !supportStore.hasTicket" class="space-y-4">
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $t('support.enterEmail') }}
                                </p>
                                <input
                                    v-model="guestEmail"
                                    type="email"
                                    :placeholder="$t('support.emailPlaceholder')"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <button
                                    @click="initTicket"
                                    :disabled="!guestEmail || supportStore.loading"
                                    class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ supportStore.loading ? $t('support.loading') : $t('support.startChat') }}
                                </button>
                            </div>

                            <!-- Чат -->
                            <div v-if="supportStore.hasTicket" class="space-y-4">
                                <!-- Сообщения -->
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="message in supportStore.ticket?.messages"
                                        :key="message.id"
                                        :class="[
                                            'flex',
                                            message.sender_type === 'client' ? 'justify-end' : 'justify-start'
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'max-w-[80%] rounded-lg px-4 py-2',
                                                message.sender_type === 'client'
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                                            ]"
                                        >
                                            <p class="text-sm whitespace-pre-wrap">{{ message.text }}</p>
                                            <p class="text-xs mt-1 opacity-70">
                                                {{ formatTime(message.created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Поле ввода -->
                                <div class="flex gap-2">
                                    <textarea
                                        v-model="messageText"
                                        :placeholder="$t('support.messagePlaceholder')"
                                        rows="3"
                                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                        @keydown.enter.exact.prevent="sendMessage"
                                        @keydown.enter.shift.exact="messageText += '\n'"
                                    />
                                    <button
                                        @click="sendMessage"
                                        :disabled="!messageText.trim() || supportStore.loading"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed self-end"
                                    >
                                        <Send class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Кнопка перехода в Telegram -->
                                <div v-if="channelSelected === 'web'" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <button
                                        @click="openTelegram"
                                        class="w-full px-4 py-2 border border-indigo-500 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <Send class="w-5 h-5" />
                                        {{ $t('support.switchToTelegram') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Ошибка -->
                            <div v-if="supportStore.error" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 text-sm">
                                {{ supportStore.error }}
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, Teleport } from 'vue';
import { X, MessageSquare, Send } from 'lucide-vue-next';
import { useSupportStore } from '@/stores/support';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const supportStore = useSupportStore();
const authStore = useAuthStore();

const isOpen = ref(false);
const channelSelected = ref<'web' | 'telegram' | null>(null);
const messageText = ref('');
const guestEmail = ref('');

const openModal = () => {
    isOpen.value = true;
    // Если уже есть тикет, загружаем его
    if (!supportStore.hasTicket && authStore.isAuthenticated) {
        initTicket();
    }
};

const closeModal = () => {
    isOpen.value = false;
    supportStore.stopPolling();
    // Не сбрасываем тикет, чтобы сохранить историю
};

const selectChannel = async (channel: 'web' | 'telegram') => {
    channelSelected.value = channel;
    
    if (channel === 'telegram') {
        // Если пользователь авторизован, создаем тикет и получаем ссылку
        if (authStore.isAuthenticated) {
            try {
                await supportStore.getOrCreateTicket();
                const telegramLink = await supportStore.getTelegramLink();
                window.open(telegramLink, '_blank');
                closeModal();
            } catch (error) {
                console.error('Error opening Telegram:', error);
            }
        } else {
            // Для гостей просто открываем бота
            const botUsername = 'your_support_bot'; // Замените на реальное имя бота
            window.open(`https://t.me/${botUsername}`, '_blank');
            closeModal();
        }
    } else {
        // Для веб-канала инициализируем тикет
        if (authStore.isAuthenticated) {
            await initTicket();
        }
    }
};

const initTicket = async () => {
    try {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        await supportStore.getOrCreateTicket(email);
        
        // Начинаем polling для новых сообщений
        supportStore.startPolling(email);
    } catch (error) {
        console.error('Error initializing ticket:', error);
    }
};

const sendMessage = async () => {
    if (!messageText.value.trim() || supportStore.loading) return;

    try {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        await supportStore.sendMessage(messageText.value.trim(), 'web', email);
        messageText.value = '';
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

const openTelegram = async () => {
    try {
        const telegramLink = await supportStore.getTelegramLink();
        window.open(telegramLink, '_blank');
    } catch (error) {
        console.error('Error getting Telegram link:', error);
    }
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

// Слушаем событие открытия модального окна
const handleOpenSupport = () => {
    openModal();
};

onMounted(() => {
    window.addEventListener('open-support-modal', handleOpenSupport);
});

onUnmounted(() => {
    window.removeEventListener('open-support-modal', handleOpenSupport);
    supportStore.stopPolling();
});

// Следим за изменениями тикета
watch(() => supportStore.hasTicket, (hasTicket) => {
    if (hasTicket && isOpen.value) {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        supportStore.startPolling(email);
    }
});
</script>


        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
                @click.self="closeModal"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="isOpen"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $t('support.title') }}
                            </h2>
                            <button
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto p-4">
                            <!-- Выбор канала (если еще не выбран) -->
                            <div v-if="!channelSelected && !supportStore.hasTicket" class="space-y-4">
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    {{ $t('support.selectChannel') }}
                                </p>
                                
                                <button
                                    @click="selectChannel('web')"
                                    class="w-full p-4 border-2 border-indigo-500 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <MessageSquare class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $t('support.channelWeb') }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $t('support.channelWebDesc') }}
                                            </p>
                                        </div>
                                    </div>
                                </button>

                                <button
                                    @click="selectChannel('telegram')"
                                    class="w-full p-4 border-2 border-indigo-500 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <Send class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $t('support.channelTelegram') }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $t('support.channelTelegramDesc') }}
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <!-- Форма email для гостей -->
                            <div v-if="!authStore.isAuthenticated && channelSelected === 'web' && !supportStore.hasTicket" class="space-y-4">
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $t('support.enterEmail') }}
                                </p>
                                <input
                                    v-model="guestEmail"
                                    type="email"
                                    :placeholder="$t('support.emailPlaceholder')"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <button
                                    @click="initTicket"
                                    :disabled="!guestEmail || supportStore.loading"
                                    class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ supportStore.loading ? $t('support.loading') : $t('support.startChat') }}
                                </button>
                            </div>

                            <!-- Чат -->
                            <div v-if="supportStore.hasTicket" class="space-y-4">
                                <!-- Сообщения -->
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="message in supportStore.ticket?.messages"
                                        :key="message.id"
                                        :class="[
                                            'flex',
                                            message.sender_type === 'client' ? 'justify-end' : 'justify-start'
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'max-w-[80%] rounded-lg px-4 py-2',
                                                message.sender_type === 'client'
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white'
                                            ]"
                                        >
                                            <p class="text-sm whitespace-pre-wrap">{{ message.text }}</p>
                                            <p class="text-xs mt-1 opacity-70">
                                                {{ formatTime(message.created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Поле ввода -->
                                <div class="flex gap-2">
                                    <textarea
                                        v-model="messageText"
                                        :placeholder="$t('support.messagePlaceholder')"
                                        rows="3"
                                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                        @keydown.enter.exact.prevent="sendMessage"
                                        @keydown.enter.shift.exact="messageText += '\n'"
                                    />
                                    <button
                                        @click="sendMessage"
                                        :disabled="!messageText.trim() || supportStore.loading"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed self-end"
                                    >
                                        <Send class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Кнопка перехода в Telegram -->
                                <div v-if="channelSelected === 'web'" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <button
                                        @click="openTelegram"
                                        class="w-full px-4 py-2 border border-indigo-500 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <Send class="w-5 h-5" />
                                        {{ $t('support.switchToTelegram') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Ошибка -->
                            <div v-if="supportStore.error" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 text-sm">
                                {{ supportStore.error }}
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, Teleport } from 'vue';
import { X, MessageSquare, Send } from 'lucide-vue-next';
import { useSupportStore } from '@/stores/support';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const supportStore = useSupportStore();
const authStore = useAuthStore();

const isOpen = ref(false);
const channelSelected = ref<'web' | 'telegram' | null>(null);
const messageText = ref('');
const guestEmail = ref('');

const openModal = () => {
    isOpen.value = true;
    // Если уже есть тикет, загружаем его
    if (!supportStore.hasTicket && authStore.isAuthenticated) {
        initTicket();
    }
};

const closeModal = () => {
    isOpen.value = false;
    supportStore.stopPolling();
    // Не сбрасываем тикет, чтобы сохранить историю
};

const selectChannel = async (channel: 'web' | 'telegram') => {
    channelSelected.value = channel;
    
    if (channel === 'telegram') {
        // Если пользователь авторизован, создаем тикет и получаем ссылку
        if (authStore.isAuthenticated) {
            try {
                await supportStore.getOrCreateTicket();
                const telegramLink = await supportStore.getTelegramLink();
                window.open(telegramLink, '_blank');
                closeModal();
            } catch (error) {
                console.error('Error opening Telegram:', error);
            }
        } else {
            // Для гостей просто открываем бота
            const botUsername = 'your_support_bot'; // Замените на реальное имя бота
            window.open(`https://t.me/${botUsername}`, '_blank');
            closeModal();
        }
    } else {
        // Для веб-канала инициализируем тикет
        if (authStore.isAuthenticated) {
            await initTicket();
        }
    }
};

const initTicket = async () => {
    try {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        await supportStore.getOrCreateTicket(email);
        
        // Начинаем polling для новых сообщений
        supportStore.startPolling(email);
    } catch (error) {
        console.error('Error initializing ticket:', error);
    }
};

const sendMessage = async () => {
    if (!messageText.value.trim() || supportStore.loading) return;

    try {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        await supportStore.sendMessage(messageText.value.trim(), 'web', email);
        messageText.value = '';
    } catch (error) {
        console.error('Error sending message:', error);
    }
};

const openTelegram = async () => {
    try {
        const telegramLink = await supportStore.getTelegramLink();
        window.open(telegramLink, '_blank');
    } catch (error) {
        console.error('Error getting Telegram link:', error);
    }
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

// Слушаем событие открытия модального окна
const handleOpenSupport = () => {
    openModal();
};

onMounted(() => {
    window.addEventListener('open-support-modal', handleOpenSupport);
});

onUnmounted(() => {
    window.removeEventListener('open-support-modal', handleOpenSupport);
    supportStore.stopPolling();
});

// Следим за изменениями тикета
watch(() => supportStore.hasTicket, (hasTicket) => {
    if (hasTicket && isOpen.value) {
        const email = authStore.isAuthenticated ? undefined : guestEmail.value;
        supportStore.startPolling(email);
    }
});
</script>

