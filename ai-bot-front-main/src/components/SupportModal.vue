<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="isVisible"
                class="modal-overlay"
                :class="{ 'no-overlay-close': step === 'chat' }"
                @click="handleOverlayClick"
            >
                <div class="modal-container" @click.stop>
                    <!-- Header -->
                    <div class="modal-header">
                        <div class="header-left">
                            <button v-if="step !== 'select'" class="back-button" @click="handleBack">
                                <ChevronLeftIcon class="w-5 h-5" />
                            </button>
                            <h2 class="modal-title gradient-text">{{ $t('support.title') }}</h2>
                        </div>
                        <div class="header-right">
                            <!-- Статус тикета (только в чате) -->
                            <div v-if="step === 'chat' && supportStore.ticket" class="ticket-status-badge" :class="supportStore.ticket.status">
                                {{ $t(`support.statuses.${supportStore.ticket.status}`) }}
                            </div>
                            <button class="close-button" @click="close">
                                <XIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="modal-body" ref="bodyRef">
                        <!-- Step 1: Select Channel -->
                        <div v-if="step === 'select'" class="step-content">
                            <p class="step-description">{{ $t('support.selectChannel') }}</p>
                            <div class="channel-options">
                                <button class="channel-card" @click="startWebChat">
                                    <div class="channel-icon web">
                                        <MessageCircleIcon class="w-6 h-6" />
                                    </div>
                                    <div class="channel-info">
                                        <h3>{{ $t('support.channelWeb') }}</h3>
                                        <p>{{ $t('support.channelWebDesc') }}</p>
                                    </div>
                                </button>

                                <button class="channel-card" @click="startTelegramChat" :disabled="channelLoading">
                                    <div class="channel-icon telegram">
                                        <SendIcon class="w-6 h-6" />
                                    </div>
                                    <div class="channel-info">
                                        <h3>{{ $t('support.channelTelegram') }}</h3>
                                        <p v-if="!channelLoading">{{ $t('support.channelTelegramDesc') }}</p>
                                        <p v-else class="animate-pulse">{{ $t('support.loading') }}</p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Email Form (for guests) -->
                        <div v-if="step === 'email'" class="step-content">
                            <p class="step-description">{{ $t('support.enterEmail') }}</p>
                            <form @submit.prevent="handleEmailSubmit" class="email-form">
                                <div class="input-group">
                                    <input
                                        v-model="email"
                                        type="email"
                                        :placeholder="$t('support.emailPlaceholder')"
                                        required
                                        class="email-input"
                                        :disabled="supportStore.loading"
                                    />
                                </div>
                                <button type="submit" class="submit-button" :disabled="supportStore.loading">
                                    <span v-if="!supportStore.loading">{{ $t('support.startChat') }}</span>
                                    <span v-else class="animate-spin">
                                        <LoaderIcon class="w-5 h-5" />
                                    </span>
                                </button>
                                <p v-if="supportStore.error" class="error-text">{{ supportStore.error }}</p>
                            </form>
                        </div>

                        <!-- Step 3: Chat -->
                        <div v-if="step === 'chat'" class="chat-container">
                            <div class="messages-list" ref="messagesRef">
                                <div
                                    v-for="msg in supportStore.messages"
                                    :key="msg.id"
                                    :class="['message-wrapper', msg.sender_type]"
                                >
                                    <div class="message-bubble">
                                        <!-- Отображение изображения, если оно есть -->
                                        <div v-if="msg.image_url" class="message-image">
                                            <img :src="msg.image_url" alt="Attached image" @click="openImage(msg.image_url)" />
                                        </div>
                                        <p v-if="msg.text" class="message-text">{{ msg.text }}</p>
                                        <span class="message-time">{{ formatTime(msg.created_at) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Системные уведомления в чате -->
                                <div v-for="(notif, idx) in systemNotifications" :key="'notif-'+idx" class="system-notification">
                                    {{ notif.text }}
                                </div>
                            </div>

                            <!-- Image Preview Area -->
                            <div v-if="imagePreviewUrl" class="image-preview-bar">
                                <div class="preview-container">
                                    <img :src="imagePreviewUrl" alt="Preview" />
                                    <button class="remove-preview" @click="removeImagePreview">
                                        <XIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="chat-footer">
                                <div class="input-container">
                                    <!-- Кнопка прикрепления изображения -->
                                    <button class="attach-button" @click="triggerImageSelect" :disabled="supportStore.loading">
                                        <PaperclipIcon class="w-5 h-5" />
                                    </button>
                                    <input type="file" ref="imageInputRef" @change="handleImageSelect" class="hidden" accept="image/*">
                                    
                                    <textarea
                                        v-model="messageText"
                                        :placeholder="$t('support.messagePlaceholder')"
                                        @keydown.enter.exact.prevent="handleSendMessage"
                                        rows="1"
                                        ref="textareaRef"
                                        @input="adjustTextarea"
                                    ></textarea>
                                    <button
                                        class="send-button"
                                        @click="handleSendMessage"
                                        :disabled="(!messageText.trim() && !selectedImage) || supportStore.loading"
                                    >
                                        <SendIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Fullscreen Image Preview -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="fullImage" class="fullscreen-preview" @click="fullImage = null">
                <img :src="fullImage" @click.stop />
                <button class="close-preview" @click="fullImage = null">
                    <XIcon class="w-8 h-8" />
                </button>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import {
    XIcon,
    ChevronLeftIcon,
    MessageCircleIcon,
    SendIcon,
    LoaderIcon,
    PaperclipIcon
} from 'lucide-vue-next';
import { useSupportStore } from '@/stores/support';
import { useAuthStore } from '@/stores/auth';
import { useI18n } from 'vue-i18n';

const isVisible = ref(false);
const step = ref<'select' | 'email' | 'chat'>('select');
const email = ref('');
const messageText = ref('');
const bodyRef = ref<HTMLElement | null>(null);
const messagesRef = ref<HTMLElement | null>(null);
const textareaRef = ref<HTMLTextAreaElement | null>(null);
const channelLoading = ref(false);

// Изображения
const imageInputRef = ref<HTMLInputElement | null>(null);
const selectedImage = ref<File | null>(null);
const imagePreviewUrl = ref<string | null>(null);
const fullImage = ref<string | null>(null);

// Системные уведомления
const systemNotifications = ref<{text: string}[]>([]);

const supportStore = useSupportStore();
const authStore = useAuthStore();
const { t } = useI18n();

// Открыть модал
const open = async () => {
    isVisible.value = true;
    supportStore.setModalVisible(true);
    
    if (authStore.isAuthenticated) {
        await supportStore.ensureTicket();
        step.value = 'chat';
        startPolling();
    } else {
        const savedEmail = localStorage.getItem('support_guest_email');
        if (savedEmail) {
            email.value = savedEmail;
            await supportStore.ensureTicket(savedEmail);
            step.value = 'chat';
            startPolling();
        } else {
            step.value = 'select';
        }
    }
    
    await nextTick();
    scrollToBottom();
};

const close = () => {
    isVisible.value = false;
    supportStore.setModalVisible(false);
    stopPolling();
};

// Обработка клика по оверлею
const handleOverlayClick = () => {
    if (!channelLoading.value) {
        close();
    }
};

const handleBack = () => {
    if (step.value === 'email') step.value = 'select';
    else if (step.value === 'chat') {
        if (!authStore.isAuthenticated && !localStorage.getItem('support_guest_email')) {
            step.value = 'email';
        } else {
            step.value = 'select';
        }
    }
};

const startWebChat = () => {
    if (authStore.isAuthenticated) {
        step.value = 'chat';
        startPolling();
    } else {
        const savedEmail = localStorage.getItem('support_guest_email');
        if (savedEmail) {
            step.value = 'chat';
            startPolling();
        } else {
            step.value = 'email';
        }
    }
};

const startTelegramChat = async () => {
    channelLoading.value = true;
    try {
        await supportStore.ensureTicket(undefined, true);
        if (supportStore.ticket) {
            const response = await fetch(`/api/support/ticket/${supportStore.ticket.id}/telegram-link`);
            const data = await response.json();
            if (data.success && data.telegram_link) {
                window.open(data.telegram_link, '_blank');
            }
        }
    } catch (e) {
        console.error('Telegram redirect error', e);
    } finally {
        channelLoading.value = false;
    }
};

const handleEmailSubmit = async () => {
    if (!email.value) return;
    const success = await supportStore.ensureTicket(email.value);
    if (success) {
        step.value = 'chat';
        startPolling();
        await nextTick();
        scrollToBottom();
    }
};

const handleSendMessage = async () => {
    if ((!messageText.value.trim() && !selectedImage.value) || supportStore.loading) return;
    
    const text = messageText.value;
    const file = selectedImage.value || undefined;
    
    // Очищаем сразу для UI отзывчивости
    messageText.value = '';
    removeImagePreview();
    if (textareaRef.value) textareaRef.value.style.height = 'auto';

    const success = await supportStore.send(text, 'web', email.value, file);
    if (success) {
        await nextTick();
        scrollToBottom();
    }
};

// Работа с изображениями
const triggerImageSelect = () => {
    imageInputRef.value?.click();
};

const handleImageSelect = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert(t('support.errors.imageSizeExceeded'));
            return;
        }
        selectedImage.value = file;
        imagePreviewUrl.value = URL.createObjectURL(file);
    }
};

const removeImagePreview = () => {
    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value);
    }
    selectedImage.value = null;
    imagePreviewUrl.value = null;
    if (imageInputRef.value) imageInputRef.value.value = '';
};

const openImage = (url: string) => {
    fullImage.value = url;
};

// Поллинг
let pollingTimer: any = null;
const startPolling = () => {
    stopPolling();
    pollingTimer = setInterval(() => {
        supportStore.fetchNew(email.value);
    }, 5000);
};

const stopPolling = () => {
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }
};

const scrollToBottom = () => {
    if (messagesRef.value) {
        messagesRef.value.scrollTop = messagesRef.value.scrollHeight;
    }
};

const adjustTextarea = () => {
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        textareaRef.value.style.height = Math.min(textareaRef.value.scrollHeight, 120) + 'px';
    }
};

const formatTime = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

// Следим за новыми сообщениями для прокрутки
watch(() => supportStore.messages.length, () => {
    nextTick(() => scrollToBottom());
});

// Слушаем системные события (звук и уведомления)
const handleSupportNotification = (e: any) => {
    if (e.detail.type === 'status' && supportStore.ticket) {
        systemNotifications.value.push({
            text: t('support.systemNotifications.statusChanged', { status: t(`support.statuses.${supportStore.ticket.status}`) })
        });
        nextTick(() => scrollToBottom());
    }
};

onMounted(() => {
    window.addEventListener('open-support-modal', open);
    window.addEventListener('support-notification', handleSupportNotification);
});

onUnmounted(() => {
    window.removeEventListener('open-support-modal', open);
    window.removeEventListener('support-notification', handleSupportNotification);
    stopPolling();
    removeImagePreview();
});

defineExpose({ open, close });
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal-container {
    width: 100%;
    max-width: 450px;
    height: 600px;
    max-height: 90vh;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.dark .modal-container {
    background: rgba(17, 24, 39, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.dark .modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
}

.gradient-text {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ticket-status-badge {
    padding: 4px 10px;
    border-radius: 99px;
    font-size: 12px;
    font-weight: 500;
}

.ticket-status-badge.open { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.ticket-status-badge.in_progress { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.ticket-status-badge.closed { background: rgba(107, 114, 128, 0.1); color: #6b7280; }

.modal-body {
    flex: 1;
    overflow-y: auto;
    position: relative;
    display: flex;
    flex-direction: column;
}

.step-content {
    padding: 30px 24px;
    text-align: center;
}

.step-description {
    color: #4b5563;
    margin-bottom: 24px;
}

.dark .step-description {
    color: #9ca3af;
}

.channel-options {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.channel-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
    text-align: left;
    width: 100%;
}

.dark .channel-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.channel-card:hover:not(:disabled) {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.channel-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.channel-icon.web { background: #6366f1; }
.channel-icon.telegram { background: #0088cc; }

.channel-info h3 {
    font-weight: 600;
    margin-bottom: 2px;
}

.channel-info p {
    font-size: 13px;
    color: #6b7280;
}

/* Chat Styles */
.chat-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.messages-list {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message-wrapper {
    display: flex;
    width: 100%;
}

.message-wrapper.client { justify-content: flex-end; }
.message-wrapper.admin { justify-content: flex-start; }

.message-bubble {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 18px;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.client .message-bubble {
    background: #6366f1;
    color: white;
    border-bottom-right-radius: 4px;
}

.admin .message-bubble {
    background: white;
    color: #1f2937;
    border-bottom-left-radius: 4px;
}

.dark .admin .message-bubble {
    background: #374151;
    color: white;
}

.message-image {
    margin-bottom: 8px;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
}

.message-image img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
}

.message-time {
    font-size: 10px;
    opacity: 0.7;
    display: block;
    margin-top: 4px;
    text-align: right;
}

.system-notification {
    text-align: center;
    font-size: 12px;
    color: #6b7280;
    margin: 8px 0;
    padding: 4px 12px;
    background: rgba(0, 0, 0, 0.03);
    border-radius: 99px;
    align-self: center;
}

.chat-footer {
    padding: 16px;
    background: rgba(255, 255, 255, 0.5);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.dark .chat-footer {
    background: rgba(0, 0, 0, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.input-container {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    background: white;
    border-radius: 20px;
    padding: 8px 12px;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.dark .input-container {
    background: #1f2937;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.input-container textarea {
    flex: 1;
    border: none;
    background: transparent;
    padding: 4px;
    resize: none;
    max-height: 120px;
    font-size: 14px;
    outline: none;
}

.image-preview-bar {
    padding: 8px 16px;
    background: rgba(0, 0, 0, 0.02);
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.preview-container {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.preview-container img {
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
    border-radius: 50%;
    padding: 2px;
}

.send-button, .attach-button {
    padding: 6px;
    border-radius: 50%;
    transition: all 0.2s;
}

.send-button { color: #6366f1; }
.send-button:disabled { opacity: 0.3; }
.attach-button { color: #6b7280; }

.email-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.email-input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    background: white;
    outline: none;
}

.dark .email-input {
    background: #1f2937;
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

.submit-button {
    background: #6366f1;
    color: white;
    padding: 12px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.submit-button:hover { background: #4f46e5; }

/* Preview */
.fullscreen-preview {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 11000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.fullscreen-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.close-preview {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
}

/* Animations */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
