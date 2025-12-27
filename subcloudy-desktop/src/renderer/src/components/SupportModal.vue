<template>
    <div 
        v-if="supportStore.isModalVisible" 
        class="support-modal-container"
        ref="modalContainerRef"
        @mousedown.stop
        @touchstart.stop
    >
        <!-- Liquid Glass Effect -->
        <div class="liquid-glass-effect"></div>
        <div class="liquid-glass-tint"></div>
        <div class="liquid-glass-shine"></div>
        
        <div class="modal-header">
            <div class="header-left">
                <button 
                    v-if="step !== 'channel'" 
                    class="back-button" 
                    type="button" 
                    @click="handleBack"
                    :aria-label="t('support.back')"
                >
                    <svg class="back-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h2 class="modal-title gradient-text">{{ t('support.title') }}</h2>
                <div v-if="ticket && step === 'chat'" class="ticket-status-badge" :class="`status-${ticket.status}`">
                    {{ t(`support.statuses.${ticket.status}`) }}
                </div>
            </div>
            <button class="close-button" type="button" :aria-label="t('support.close')" @click="() => handleClose(true)">
                <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="modal-content">
            <div v-if="step === 'channel'" class="step-container">
                <p class="step-description">{{ t('support.selectChannel') }}</p>
                <div class="channels-grid">
                    <button 
                        type="button" 
                        class="channel-card" 
                        :disabled="channelLoading !== null"
                        @click="handleChannelClick('web', $event)"
                    >
                        <div class="channel-icon-wrapper">
                            <div v-if="channelLoading === 'web'" class="channel-loading">
                                <span class="button-spinner"></span>
                            </div>
                            <svg v-else class="channel-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="channel-title">{{ t('support.channelWeb') }}</h3>
                        <p class="channel-description">{{ t('support.channelWebDesc') }}</p>
                    </button>

                    <button 
                        type="button" 
                        class="channel-card" 
                        :disabled="channelLoading !== null"
                        @click="handleChannelClick('telegram', $event)"
                    >
                        <div class="channel-icon-wrapper">
                            <div v-if="channelLoading === 'telegram'" class="channel-loading">
                                <span class="button-spinner"></span>
                            </div>
                            <svg v-else class="channel-icon" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                            </svg>
                        </div>
                        <h3 class="channel-title">{{ t('support.channelTelegram') }}</h3>
                        <p class="channel-description">{{ t('support.channelTelegramDesc') }}</p>
                    </button>
                </div>
            </div>

            <div v-else-if="step === 'email'" class="step-container">
                <p class="step-description">{{ safeT('support.enterEmail', 'Введите ваш email для продолжения') }}</p>
                <div class="email-form">
                    <input
                        ref="emailInputRef"
                        v-model="email"
                        type="email"
                        :placeholder="safeT('support.emailPlaceholder', 'your@email.com')"
                        class="email-input"
                        @keyup.enter="handleEmailSubmit"
                    />
                    <button
                        type="button"
                        class="submit-button"
                        :disabled="!isEmailValid || isLoading"
                        @click.stop="handleEmailSubmit"
                    >
                        <span v-if="isLoading" class="button-spinner"></span>
                        <span v-else>{{ safeT('support.startChat', 'Начать чат') }}</span>
                    </button>
                </div>
            </div>

            <div v-else-if="step === 'chat'" class="step-container">
                <div class="messages-wrapper" ref="messagesRef">
                    <div v-if="isLoading && (!messages || !messages.length)" class="loading-state">
                        <div class="spinner"></div>
                        <p>{{ safeT('support.loading', 'Загрузка сообщений...') }}</p>
                    </div>
                    <div v-else-if="!messages || !messages.length" class="empty-state">
                        <p>{{ safeT('support.noMessages', 'Нет сообщений. Напишите нам!') }}</p>
                    </div>
                    <div v-else class="messages-list">
                        <div 
                            v-for="(msg, index) in messages" 
                            :key="msg.id || index"
                            :class="['message-item', `message-${msg.sender_type}`, { 'system-msg-container': !msg.text && !msg.image_url }]"
                        >
                            <div v-if="shouldShowStatusNotification(msg, index)" class="system-notification">
                                {{ t('support.systemNotifications.statusChanged', { status: t(`support.statuses.${msg.ticket_status}`) }) }}
                            </div>

                            <div v-if="msg.text || msg.image_url" class="message-bubble shadow-sm">
                                <div v-if="msg.image_url" class="message-image-wrapper">
                                    <img 
                                        :src="msg.image_url" 
                                        alt="Support attachment" 
                                        class="message-image"
                                        @click="openImage(msg.image_url)"
                                    />
                                </div>
                                <div v-if="msg.text" class="message-text">{{ msg.text }}</div>
                                <span class="message-time">{{ formatTime(msg.created_at) }}</span>
                            </div>
                        </div>

                        <div v-if="ticket?.other_channel_reply" class="system-notification other-channel-alert">
                            {{ t('support.systemNotifications.adminRepliedIn', { channel: ticket.other_channel_reply === 'telegram' ? 'Telegram' : 'Web' }) }}
                        </div>

                        <div v-if="ticket?.status === 'closed'" class="system-notification closed-ticket-alert">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ t('support.systemNotifications.ticketClosed') }}
                        </div>
                    </div>
                </div>

                <div v-if="error" class="error-message">
                    <svg class="error-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ translateError(error) }}
                </div>

                <div v-if="previewUrl" class="image-preview-container">
                    <div class="preview-wrapper">
                        <img :src="previewUrl" alt="Preview" class="preview-image" />
                        <button class="remove-preview" @click="removeFile">
                            <XIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div 
                    class="message-input-wrapper"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="{ 'dragging': isDragging }"
                >
                    <button class="attach-button" @click="triggerFileInput" :disabled="isLoading">
                        <PaperclipIcon class="attach-icon" />
                    </button>
                    <input 
                        type="file" 
                        ref="fileInput" 
                        class="hidden" 
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" 
                        @change="handleFileChange"
                    />
                    
                    <input
                        v-model="messageText"
                        type="text"
                        :placeholder="isDragging ? 'Отпустите файл чтобы загрузить' : safeT('support.messagePlaceholder', 'Напишите сообщение...')"
                        class="message-input"
                        @keyup.enter="handleSendMessage"
                        :disabled="isLoading"
                    />
                    <button 
                        class="send-button" 
                        @click="handleSendMessage" 
                        :disabled="isLoading || (!messageText.trim() && !selectedFile)"
                    >
                        <SendIcon class="send-icon" />
                    </button>
                </div>

                <button 
                    v-if="selectedChannel === 'web'"
                    class="telegram-link-button"
                    @click="handleOpenTelegram"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                    </svg>
                    {{ safeT('support.switchToTelegram', 'Открыть в Telegram') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import { useSupportStore } from '../stores/support';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';
import { SendIcon, PaperclipIcon, XIcon } from 'lucide-vue-next';

const { t } = useI18n();
const supportStore = useSupportStore();
const authStore = useAuthStore();

const step = ref<'channel' | 'email' | 'chat'>('channel');
const selectedChannel = ref<'web' | 'telegram' | null>(null);
const channelLoading = ref<'web' | 'telegram' | null>(null);
const email = ref('');
const messageText = ref('');
const messagesRef = ref<HTMLElement | null>(null);
const emailInputRef = ref<HTMLInputElement | null>(null);
const modalContainerRef = ref<HTMLElement | null>(null);

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const previewUrl = ref<string | null>(null);
const isDragging = ref(false);

const messages = computed(() => supportStore.ticket?.messages || []);
const ticket = computed(() => supportStore.ticket);
const isLoading = computed(() => supportStore.loading);
const error = computed(() => supportStore.error);

const isEmailValid = computed(() => {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email.value.toLowerCase());
});

const safeT = (key: string, fallback: string) => {
    try {
        const res = t(key);
        return res === key ? fallback : res;
    } catch {
        return fallback;
    }
};

const open = async () => {
    supportStore.setModalVisible(true);
    
    if (supportStore.ticket && step.value === 'chat') {
        selectedChannel.value = 'web';
        supportStore.startPolling(authStore.isAuthenticated ? undefined : supportStore.guestEmail || undefined);
        await nextTick();
        scrollToBottom();
        return;
    }

    step.value = 'channel';
    selectedChannel.value = null;
    email.value = authStore.isAuthenticated ? (authStore.user?.email || '') : (localStorage.getItem('support_guest_email') || '');
    messageText.value = '';
};

defineExpose({ open });

const handleOpenSupportEvent = () => open();

const handleBack = () => {
    if (step.value === 'chat') {
        step.value = 'channel';
        supportStore.stopPolling();
    } else if (step.value === 'email') {
        step.value = 'channel';
    }
};

const handleChannelClick = async (channel: 'web' | 'telegram', event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    if (!supportStore.isModalVisible) {
        supportStore.setModalVisible(true);
    }
    
    selectedChannel.value = channel;
    
    if (channel === 'telegram') {
        const botUsername = 'subcloudy_support_bot';
        window.open(`https://t.me/${botUsername}`, '_blank');
        handleClose(true);
        return;
    }

    channelLoading.value = channel;
    
    try {
        if (!authStore.isAuthenticated) {
            step.value = 'email';
            await nextTick();
            await new Promise(resolve => setTimeout(resolve, 50));
            emailInputRef.value?.focus();
        } else {
            await initializeChat();
        }
    } catch (error) {
        console.error('[SupportModal] Error in handleChannelClick', error);
    } finally {
        channelLoading.value = null;
    }
};

const handleEmailSubmit = async () => {
    if (!isEmailValid.value || isLoading.value) return;
    localStorage.setItem('support_guest_email', email.value.trim());
    await initializeChat();
};

const initializeChat = async () => {
    try {
        const emailValue = authStore.isAuthenticated ? undefined : email.value.trim();
        const isTelegramGuest = selectedChannel.value === 'telegram' && !authStore.isAuthenticated;
        
        const ok = await supportStore.ensureTicket(emailValue, isTelegramGuest);
        
        if (!ok) {
            step.value = authStore.isAuthenticated ? 'channel' : 'email';
            return;
        }

        if (selectedChannel.value === 'telegram') {
            handleTelegramRedirect();
            return;
        }

        step.value = 'chat';
        supportStore.startPolling(emailValue);
        await nextTick();
        scrollToBottom();
    } catch (err) {
        console.error('[SupportModal] initializeChat: FATAL ERROR', err);
    }
};

const triggerFileInput = () => fileInput.value?.click();

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files?.length) {
        processFile(input.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files?.length) {
        const file = event.dataTransfer.files[0];
        processFile(file);
    }
};

const processFile = (file: File) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Допустимы только изображения (JPEG, PNG, GIF, WebP)');
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert('Размер файла не должен превышать 5MB');
        return;
    }
    selectedFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
};

const removeFile = () => {
    selectedFile.value = null;
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    if (fileInput.value) fileInput.value.value = '';
};

const openImage = (url: string) => window.open(url, '_blank');

const shouldShowStatusNotification = (message: any, index: number) => {
    if (index === 0) return false;
    const prevMsg = messages.value[index - 1];
    return message.ticket_status && prevMsg.ticket_status && message.ticket_status !== prevMsg.ticket_status;
};

const handleSendMessage = async () => {
    if ((!messageText.value.trim() && !selectedFile.value) || isLoading.value || !ticket.value?.id) return;
    
    const text = messageText.value.trim();
    const file = selectedFile.value || undefined;
    
    messageText.value = '';
    removeFile();
    
    const emailValue = authStore.isAuthenticated ? undefined : email.value.trim();
    await supportStore.send(text, 'web', emailValue, file);
    await nextTick();
    scrollToBottom();
};

const handleTelegramRedirect = () => {
    const botUsername = 'subcloudy_support_bot';
    window.open(`https://t.me/${botUsername}`, '_blank');
    supportStore.stopPolling();
    handleClose(true);
};

const handleOpenTelegram = () => handleTelegramRedirect();

const handleClose = (forceClose = false) => {
    if (forceClose || (step.value !== 'channel' && step.value !== 'email')) {
        supportStore.setModalVisible(false);
    }
};

const scrollToBottom = () => {
    if (messagesRef.value) {
        requestAnimationFrame(() => {
            if (messagesRef.value) {
                messagesRef.value.scrollTop = messagesRef.value.scrollHeight;
            }
        });
    }
};

const formatTime = (iso: string) => new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

const translateError = (error: string | null): string => {
    if (!error) return '';
    const errorKeys: Record<string, string> = {
        'Ticket not found': 'support.errors.ticketNotFound',
        'Не удалось создать тикет': 'support.errors.createTicketFailed',
        'Access denied (session mismatch)': 'support.errors.accessDeniedSession',
        'Access denied (email mismatch)': 'support.errors.accessDeniedEmail',
    };
    const key = errorKeys[error];
    return key ? t(key) : error;
};

const handleNotificationEvent = (event: any) => {
    console.log('Support notification:', event.detail.type);
};

watch(() => messages.value?.length ?? 0, () => {
    if (messages.value && Array.isArray(messages.value)) {
        nextTick(scrollToBottom);
    }
});

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && supportStore.isModalVisible) {
        handleClose(true);
    }
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (modalContainerRef.value && !modalContainerRef.value.contains(target) && !target.closest('.submit-button')) {
        if (step.value !== 'chat' && supportStore.isModalVisible) {
            handleClose(true);
        }
    }
};

const handleVisibilityChange = () => {
    if (document.hidden) {
        supportStore.setPollingInterval(15000);
    } else {
        if (supportStore.isModalVisible && step.value === 'chat') {
            const pollEmail = authStore.isAuthenticated ? undefined : (supportStore.guestEmail || localStorage.getItem('support_guest_email') || undefined);
            supportStore.fetchNew(pollEmail).then(() => {
                supportStore.setPollingInterval(3000);
            });
        }
    }
};

watch(() => authStore.isAuthenticated, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        supportStore.reset();
        step.value = 'channel';
    }
});

onMounted(() => {
    window.addEventListener('open-support-modal', handleOpenSupportEvent);
    window.addEventListener('keydown', handleEscape);
    window.addEventListener('mousedown', handleClickOutside);
    window.addEventListener('support-notification', handleNotificationEvent);
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onBeforeUnmount(() => {
    window.removeEventListener('open-support-modal', handleOpenSupportEvent);
    window.removeEventListener('keydown', handleEscape);
    window.removeEventListener('mousedown', handleClickOutside);
    window.removeEventListener('support-notification', handleNotificationEvent);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    supportStore.stopPolling();
});
</script>

<style scoped>
.support-modal-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 10000;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 1.25rem;
    width: 100%;
    max-width: 400px;
    height: 600px;
    max-height: calc(100vh - 100px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    pointer-events: auto;
}

.dark .support-modal-container {
    background: rgba(31, 41, 55, 0.7);
    border-color: rgba(255, 255, 255, 0.1);
}

.liquid-glass-effect {
    position: absolute;
    z-index: -1;
    inset: 0;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-radius: 1.25rem;
}

.liquid-glass-tint {
    position: absolute;
    z-index: -1;
    inset: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 1.25rem;
}

.dark .liquid-glass-tint {
    background: rgba(31, 41, 55, 0.3);
}

.liquid-glass-shine {
    position: absolute;
    z-index: -1;
    inset: 0;
    border-radius: 1.25rem;
    box-shadow: 
        inset 2px 2px 1px 0 rgba(255, 255, 255, 0.2),
        inset -1px -1px 1px 1px rgba(255, 255, 255, 0.1);
}

.modal-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.dark .modal-header { border-bottom-color: rgba(255, 255, 255, 0.05); }

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.back-button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: rgb(107, 114, 128);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.back-button:hover {
    color: rgb(99, 102, 241);
    transform: translateX(-2px);
}

.dark .back-button { color: rgb(209, 213, 219); }
.dark .back-button:hover { color: white; }

.back-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.modal-title { font-size: 1.15rem; font-weight: 600; color: rgb(17, 24, 39); }
.dark .modal-title { color: white; }

.ticket-status-badge {
    font-size: 0.7rem;
    padding: 0.15rem 0.5rem;
    border-radius: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 0.5rem;
}

.status-open {
    background: rgba(245, 158, 11, 0.1);
    color: rgb(245, 158, 11);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.status-in_progress {
    background: rgba(59, 130, 246, 0.1);
    color: rgb(59, 130, 246);
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.status-closed {
    background: rgba(16, 185, 129, 0.1);
    color: rgb(16, 185, 129);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.close-button { background: none; border: none; cursor: pointer; padding: 0.5rem; margin: -0.5rem; color: rgb(107, 114, 128); transition: all 0.2s; }
.close-button:hover { color: rgb(17, 24, 39); }
.dark .close-button { color: rgb(209, 213, 219); }
.dark .close-button:hover { color: white; }
.close-icon { width: 1.25rem; height: 1.25rem; }

.modal-content { padding: 1.25rem; flex-grow: 1; overflow-y: auto; position: relative; z-index: 1; }
.step-container { display: flex; flex-direction: column; height: 100%; }
.step-description { color: rgb(107, 114, 128); margin-bottom: 1.25rem; font-size: 0.95rem; }
.dark .step-description { color: rgb(209, 213, 219); }

.channels-grid { display: flex; flex-direction: column; gap: 1rem; }
.channel-card {
    padding: 1.25rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.5);
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.dark .channel-card { border-color: rgba(255, 255, 255, 0.05); background: rgba(31, 41, 55, 0.5); }
.channel-card:hover:not(:disabled) { 
    border-color: rgb(99, 102, 241); 
    background: rgba(255, 255, 255, 0.8);
    transform: translateY(-2px); 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
}
.dark .channel-card:hover:not(:disabled) {
    background: rgba(31, 41, 55, 0.8);
}
.channel-card:disabled { opacity: 0.6; cursor: not-allowed; }

.channel-icon-wrapper { display: flex; align-items: center; }
.channel-icon { width: 1.75rem; height: 1.75rem; color: rgb(99, 102, 241); }
.channel-title { font-size: 1.05rem; font-weight: 600; color: rgb(17, 24, 39); }
.dark .channel-title { color: white; }
.channel-description { font-size: 0.85rem; color: rgb(107, 114, 128); }
.dark .channel-description { color: rgb(209, 213, 219); }

.email-form { display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1rem; }
.email-input {
    width: 100%;
    padding: 0.85rem 1.15rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 0.85rem;
    background: rgba(255, 255, 255, 0.6);
    color: rgb(17, 24, 39);
    font-size: 1rem;
    outline: none;
    transition: all 0.2s;
}
.dark .email-input { border-color: rgba(255, 255, 255, 0.1); background: rgba(31, 41, 55, 0.6); color: white; }
.email-input:focus { border-color: rgb(99, 102, 241); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

.submit-button {
    padding: 0.85rem 1.25rem;
    background: linear-gradient(135deg, rgb(99, 102, 241), rgb(79, 70, 229));
    color: white;
    font-weight: 600;
    border-radius: 0.85rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.submit-button:hover:not(:disabled) { 
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); 
}
.submit-button:disabled { opacity: 0.5; cursor: not-allowed; }

.messages-wrapper {
    flex-grow: 1;
    overflow-y: auto;
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.03);
    border-radius: 1rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
}
.dark .messages-wrapper { background: rgba(255, 255, 255, 0.03); }

.loading-state, .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: rgb(107, 114, 128); gap: 1rem; }
.spinner { width: 2.25rem; height: 2.25rem; border: 3px solid rgba(99, 102, 241, 0.1); border-top-color: rgb(99, 102, 241); border-radius: 50%; animation: spin 1s linear infinite; }

.messages-list { display: flex; flex-direction: column; gap: 1rem; }
.message-item { display: flex; flex-direction: column; }
.message-item.message-admin { align-items: flex-start; }
.message-item.message-client { align-items: flex-end; }
.message-item.system-msg-container { align-items: center; width: 100%; }
.message-bubble { max-width: 85%; padding: 0.75rem 1rem; border-radius: 1.15rem; position: relative; }

.message-image-wrapper {
    margin-bottom: 0.5rem;
    cursor: pointer;
    overflow: hidden;
    border-radius: 0.75rem;
}

.message-image {
    max-width: 100%;
    max-height: 250px;
    display: block;
    transition: transform 0.2s;
}

.message-image:hover {
    transform: scale(1.02);
}

.system-notification {
    text-align: center;
    font-size: 0.75rem;
    color: rgb(107, 114, 128);
    background: rgba(0, 0, 0, 0.05);
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    margin: 0.5rem auto;
    width: fit-content;
}

.other-channel-alert {
    background: rgba(99, 102, 241, 0.1);
    color: rgb(99, 102, 241);
    border: 1px solid rgba(99, 102, 241, 0.2);
    font-weight: 500;
}

.dark .system-notification {
    color: rgb(156, 163, 175);
    background: rgba(255, 255, 255, 0.05);
}

.image-preview-container {
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.03);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 0.5rem;
}

.preview-wrapper {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid rgba(99, 102, 241, 0.3);
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-preview {
    position: absolute;
    top: 2px;
    right: 2px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    border-radius: 50%;
    padding: 2px;
    cursor: pointer;
}

.attach-button {
    background: none;
    border: none;
    padding: 0.5rem;
    color: rgb(107, 114, 128);
    cursor: pointer;
    transition: color 0.2s;
}

.attach-button:hover:not(:disabled) {
    color: rgb(99, 102, 241);
}

.attach-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.hidden { display: none; }

.message-item.message-admin .message-bubble { background: rgb(99, 102, 241); color: white; border-bottom-left-radius: 0.35rem; }
.message-item.message-client .message-bubble { background: white; color: rgb(17, 24, 39); border-bottom-right-radius: 0.35rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
.dark .message-item.message-client .message-bubble { background: rgb(55, 65, 81); color: white; }
.message-text { font-size: 0.925rem; white-space: pre-wrap; line-height: 1.4; }
.message-time { font-size: 0.7rem; opacity: 0.6; display: block; margin-top: 0.25rem; text-align: right; }

.error-message {
    padding: 0.75rem 1rem;
    background: rgba(185, 28, 28, 0.05);
    border: 1px solid rgba(185, 28, 28, 0.1);
    border-radius: 0.75rem;
    color: rgb(185, 28, 28);
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.dark .error-message { background: rgba(185, 28, 28, 0.1); color: rgb(254, 202, 202); }
.error-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }

.message-input-wrapper { 
    display: flex; 
    gap: 0.75rem; 
    align-items: center; 
    transition: all 0.3s;
    border-radius: 2rem;
    padding: 0.25rem;
}
.message-input-wrapper.dragging {
    background: rgba(99, 102, 241, 0.1);
    border: 2px dashed rgb(99, 102, 241);
}
.message-input {
    flex: 1;
    padding: 0.85rem 1.15rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 2rem;
    background: rgba(255, 255, 255, 0.6);
    color: rgb(17, 24, 39);
    outline: none;
    transition: all 0.2s;
    font-size: 0.95rem;
}
.dark .message-input { border-color: rgba(255, 255, 255, 0.1); background: rgba(31, 41, 55, 0.6); color: white; }
.message-input:focus { border-color: rgb(99, 102, 241); }

.send-button {
    width: 2.75rem; height: 2.75rem;
    display: flex; align-items: center; justify-content: center;
    background: rgb(99, 102, 241);
    color: white; border: none; border-radius: 50%; cursor: pointer; transition: all 0.2s;
    flex-shrink: 0;
}
.send-button:hover:not(:disabled) { transform: scale(1.05); background: rgb(79, 70, 229); }
.send-button:disabled { opacity: 0.5; cursor: not-allowed; }
.send-icon { width: 1.15rem; height: 1.15rem; }

.telegram-link-button {
    width: 100%;
    display: flex; align-items: center; justify-content: center;
    gap: 0.65rem; padding: 0.85rem 1rem;
    background: rgba(37, 99, 235, 0.1); color: rgb(37, 99, 235);
    border: none; border-radius: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
    margin-top: 0.5rem;
}
.dark .telegram-link-button { background: rgba(37, 99, 235, 0.2); color: rgb(147, 197, 253); }
.telegram-link-button:hover { background: rgba(37, 99, 235, 0.15); }

.messages-wrapper::-webkit-scrollbar { width: 4px; }
.messages-wrapper::-webkit-scrollbar-track { background: transparent; }
.messages-wrapper::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.1); border-radius: 10px; }
.dark .messages-wrapper::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); }

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 480px) {
    .support-modal-container { 
        bottom: 0;
        right: 0;
        left: 0;
        top: 0;
        height: 100vh; 
        max-height: 100vh; 
        border-radius: 0; 
        max-width: 100%; 
        width: 100%;
    }
}

.closed-ticket-alert {
    background: rgba(156, 163, 175, 0.1);
    color: rgb(107, 114, 128);
    border: 1px solid rgba(156, 163, 175, 0.2);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    max-width: 90%;
}

.dark .closed-ticket-alert {
    background: rgba(156, 163, 175, 0.05);
    color: rgb(156, 163, 175);
}

.gradient-text {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.button-spinner {
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    display: inline-block;
}

.channel-loading .button-spinner {
    border-top-color: rgb(99, 102, 241);
}
</style>
