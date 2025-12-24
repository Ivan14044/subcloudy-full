import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

interface TicketMessage {
    id: number;
    sender_type: 'client' | 'admin';
    source: 'web' | 'telegram';
    text: string;
    created_at: string;
}

interface Ticket {
    id: number;
    status: 'open' | 'in_progress' | 'closed';
    subject?: string;
    messages: TicketMessage[];
}

interface SupportState {
    ticket: Ticket | null;
    guestEmail: string | null;
    isModalVisible: boolean;
    loading: boolean;
    error: string | null;
    pollingId: number | null;
    lastMessageId: number | null;
    unreadCount: number;
    lastStatus: string | null;
}

export const useSupportStore = defineStore('support', {
    state: (): SupportState => ({
        ticket: null,
        guestEmail: localStorage.getItem('support_guest_email'),
        isModalVisible: false,
        loading: false,
        error: null,
        pollingId: null,
        lastMessageId: null,
        unreadCount: 0,
        lastStatus: null,
    }),

    getters: {
        hasTicket: (state) => !!state.ticket,
        ticketId: (state) => state.ticket?.id ?? null,
        messages: (state) => state.ticket?.messages ?? [],
    },

    actions: {
        // Создаем или получаем тикет
        async ensureTicket(email?: string, isTelegramGuest: boolean = false): Promise<boolean> {
            this.loading = true;
            this.error = null;
            try {
                const auth = useAuthStore();
                const savedTicketId = localStorage.getItem('support_ticket_id');
                const savedEmail = localStorage.getItem('support_guest_email');
                
                // Если передан новый email, который отличается от сохраненного,
                // игнорируем старый ID тикета и очищаем текущий тикет в сторе
                let ticketIdToTry = savedTicketId;
                if (email && savedEmail && email.trim().toLowerCase() !== savedEmail.trim().toLowerCase()) {
                    ticketIdToTry = null;
                    this.ticket = null;
                    this.lastMessageId = null;
                }

                const effectiveEmail = email || this.guestEmail || undefined;
                
                let response;
                
                if (auth.isAuthenticated) {
                    response = await axios.get('/support/ticket');
                } else if (ticketIdToTry) {
                    // Пытаемся получить существующий тикет по ID для гостя
                    try {
                        response = await axios.get(`/support/ticket/${ticketIdToTry}`, {
                            params: { email: effectiveEmail }
                        });
                    } catch (e) {
                        // Если по ID не нашли или доступ запрещен, пробуем создать/получить по email
                        const payload: any = { email: effectiveEmail };
                        if (isTelegramGuest) payload.source = 'telegram';
                        response = await axios.post('/support/ticket', payload);
                    }
                } else {
                    const payload: any = { email: effectiveEmail };
                    if (isTelegramGuest) payload.source = 'telegram';
                    response = await axios.post('/support/ticket', payload);
                }

                if (!response.data?.success || !response.data?.ticket) {
                    this.error = response.data?.error || 'Не удалось создать тикет';
                    return false;
                }

                this.ticket = response.data.ticket;
                this.lastStatus = this.ticket?.status || null;
                
                // Сохраняем данные для гостей для персистентности
                if (!auth.isAuthenticated) {
                    if (effectiveEmail) {
                        this.guestEmail = effectiveEmail;
                        localStorage.setItem('support_guest_email', effectiveEmail);
                    }
                    localStorage.setItem('support_ticket_id', String(this.ticket.id));
                }

                const msgs = this.ticket.messages || [];
                this.lastMessageId = msgs.length ? msgs[msgs.length - 1].id : null;
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.error || error.message || 'Ошибка при создании тикета';
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Отправка сообщения (с поддержкой изображений)
        async send(text: string, source: 'web' | 'telegram' = 'web', email?: string, file?: File): Promise<boolean> {
            if (!this.ticket) {
                this.error = 'Тикет не найден';
                return false;
            }

            this.loading = true;
            this.error = null;
            try {
                const auth = useAuthStore();
                
                let response;
                if (file) {
                    const formData = new FormData();
                    formData.append('text', text || '');
                    formData.append('source', source);
                    if (!auth.isAuthenticated && email) {
                        formData.append('email', email);
                    }
                    formData.append('image', file);
                    response = await axios.post(`/support/ticket/${this.ticket.id}/message`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    const payload: any = { text, source };
                    if (!auth.isAuthenticated && email) {
                        payload.email = email;
                    }
                    response = await axios.post(`/support/ticket/${this.ticket.id}/message`, payload);
                }

                if (!response.data?.success || !response.data?.message) {
                    this.error = response.data?.error || 'Не удалось отправить сообщение';
                    return false;
                }

                if (!this.ticket.messages) {
                    this.ticket.messages = [];
                }
                this.ticket.messages.push(response.data.message);
                this.lastMessageId = response.data.message.id;
                
                if (response.data.ticket?.status && this.ticket.status !== response.data.ticket.status) {
                    this.ticket.status = response.data.ticket.status;
                    this.lastStatus = this.ticket.status;
                }
                return true;
            } catch (error: any) {
                this.error = error.response?.data?.error || error.message || 'Ошибка при отправке сообщения';
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Получение новых сообщений
        async fetchNew(email?: string): Promise<boolean> {
            if (!this.ticket) {
                return false;
            }
            try {
                const auth = useAuthStore();
                const params: Record<string, any> = {};
                if (!auth.isAuthenticated && email) {
                    params.email = email;
                }
                if (this.lastMessageId) {
                    params.last_message_id = this.lastMessageId;
                }

                const response = await axios.get(`/support/ticket/${this.ticket.id}/messages`, { params });
                if (!response.data?.success || !response.data?.messages) {
                    return false;
                }
                
                const newMessages = response.data.messages as TicketMessage[];
                const newStatus = response.data.status;

                let hasNewAdminMsg = false;
                let statusChanged = false;

                if (newStatus && this.lastStatus !== newStatus) {
                    statusChanged = true;
                    this.lastStatus = newStatus;
                    if (this.ticket) this.ticket.status = newStatus;
                }

                if (newMessages.length) {
                    if (!this.ticket.messages) {
                        this.ticket.messages = [];
                    }
                    this.ticket.messages.push(...newMessages);
                    this.lastMessageId = newMessages[newMessages.length - 1].id;
                    
                    // Проверяем, есть ли сообщения от админа
                    hasNewAdminMsg = newMessages.some(m => m.sender_type === 'admin');
                    
                    if (hasNewAdminMsg && !this.isModalVisible) {
                        this.unreadCount += newMessages.filter(m => m.sender_type === 'admin').length;
                    }
                }

                if (hasNewAdminMsg || statusChanged) {
                    this.triggerNotification(statusChanged ? 'status' : 'message');
                }

                return true;
            } catch (error) {
                console.warn('polling error', error);
                return false;
            }
        },

        triggerNotification(type: 'message' | 'status') {
            // Проигрываем звук
            const audio = new Audio('/sounds/notification.mp3');
            audio.play().catch(e => console.warn('Audio play failed', e));
            
            // Генерируем событие для компонента (чтобы показать уведомление в чате)
            window.dispatchEvent(new CustomEvent('support-notification', { detail: { type } }));
        },

        setModalVisible(visible: boolean) {
            this.isModalVisible = visible;
            if (visible) {
                this.unreadCount = 0; // Сбрасываем счетчик при открытии
            }
        },

        // Генерация ссылки для Telegram
        async telegramLink(): Promise<string | null> {
            if (!this.ticket) return null;
            try {
                const response = await axios.get(`/support/ticket/${this.ticket.id}/telegram-link`);
                return response.data?.telegram_link || null;
            } catch (error) {
                console.error('Failed to get telegram link', error);
                return null;
            }
        },

        // Управление поллингом
        startPolling(email?: string) {
            this.stopPolling();
            this.pollingId = window.setInterval(() => {
                this.fetchNew(email);
            }, 5000);
        },

        stopPolling() {
            if (this.pollingId) {
                clearInterval(this.pollingId);
                this.pollingId = null;
            }
        },

        reset() {
            this.stopPolling();
            this.ticket = null;
            this.error = null;
            this.lastMessageId = null;
            this.loading = false;
        },
    },
});

