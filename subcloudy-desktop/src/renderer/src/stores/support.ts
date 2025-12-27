import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

export interface TicketMessage {
    id: number;
    sender_type: 'client' | 'admin';
    source: 'web' | 'telegram';
    ticket_status: string;
    text: string | null;
    image_url: string | null;
    created_at: string;
}

export interface Ticket {
    id: number;
    user_id: number | null;
    status: 'open' | 'in_progress' | 'closed';
    subject: string;
    messages: TicketMessage[];
    other_channel_reply?: 'web' | 'telegram' | null;
}

export const useSupportStore = defineStore('support', {
    state: () => ({
        ticket: null as Ticket | null,
        guestEmail: localStorage.getItem('support_guest_email') || null,
        sessionToken: localStorage.getItem('support_session_token') || null,
        isModalVisible: false,
        loading: false,
        error: null as string | null,
        pollingId: null as any | null,
        pollingInterval: 3000,
        pollingEmail: null as string | null | undefined,
        lastMessageId: null as number | null,
        unreadCount: parseInt(localStorage.getItem('support_unread_count') || '0'),
        lastStatus: null as string | null,
    }),

    actions: {
        generateSessionToken() {
            if (!this.sessionToken) {
                this.sessionToken = 'sess_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('support_session_token', this.sessionToken);
            }
            return this.sessionToken;
        },

        async ensureTicket(email?: string, isTelegramGuest: boolean = false): Promise<boolean> {
            this.loading = true;
            this.error = null;
            
            try {
                const auth = useAuthStore();
                const ticketIdToTry = localStorage.getItem('support_ticket_id');
                
                if (!this.sessionToken && !auth.isAuthenticated) {
                    this.generateSessionToken();
                }

                if (this.ticket && ticketIdToTry && String(this.ticket.id) !== String(ticketIdToTry)) {
                    this.lastMessageId = null;
                }

                const effectiveEmail = email || this.guestEmail || undefined;
                
                let response;
                const baseParams = { 
                    channel: 'web', 
                    session_token: this.sessionToken,
                    email: effectiveEmail 
                };
                
                if (auth.isAuthenticated) {
                    response = await axios.post('/support/ticket', {
                        channel: 'web',
                        source: 'web'
                    });
                } else if (ticketIdToTry) {
                    try {
                        response = await axios.get(`/support/ticket/${ticketIdToTry}`, {
                            params: baseParams
                        });
                    } catch (e) {
                        response = await axios.post('/support/ticket', {
                            ...baseParams,
                            source: isTelegramGuest ? 'telegram' : 'web'
                        });
                    }
                } else {
                    response = await axios.post('/support/ticket', {
                        ...baseParams,
                        source: isTelegramGuest ? 'telegram' : 'web'
                    });
                }

                if (!response.data?.success || !response.data?.ticket) {
                    this.error = response.data?.error || 'Не удалось создать тикет';
                    return false;
                }

                this.ticket = response.data.ticket;
                this.lastStatus = this.ticket?.status || null;
                
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
                    formData.append('session_token', this.sessionToken || '');
                    if (!auth.isAuthenticated && email) {
                        formData.append('email', email);
                    }
                    formData.append('image', file);
                    response = await axios.post(`/support/ticket/${this.ticket.id}/message`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    const payload: any = { 
                        text, 
                        source,
                        session_token: this.sessionToken
                    };
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

        async fetchNew(email?: string): Promise<boolean> {
            if (!this.ticket) {
                return false;
            }
            
            try {
                const auth = useAuthStore();
                const params: Record<string, any> = { 
                    channel: 'web',
                    session_token: this.sessionToken 
                };
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
                const otherChannelReply = response.data.other_channel_reply;

                if (this.ticket) {
                    this.ticket.other_channel_reply = otherChannelReply;
                }

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
                    const currentIds = new Set(this.ticket.messages.map(m => m.id));
                    const filtered = newMessages.filter(m => !currentIds.has(m.id));
                    
                    if (filtered.length > 0) {
                        this.ticket.messages.push(...filtered);
                        this.lastMessageId = filtered[filtered.length - 1].id;
                        
                        hasNewAdminMsg = filtered.some(m => m.sender_type === 'admin');
                        
                        if (hasNewAdminMsg && !this.isModalVisible) {
                            this.unreadCount += filtered.filter(m => m.sender_type === 'admin').length;
                            localStorage.setItem('support_unread_count', String(this.unreadCount));
                        }
                    }
                }

                if (hasNewAdminMsg || statusChanged || (otherChannelReply && !this.isModalVisible)) {
                    const type = statusChanged ? 'status' : 'message';
                    this.triggerNotification(type);
                    
                    if (document.hidden || !this.isModalVisible) {
                        this.showSystemNotification(type);
                    }
                }

                this.error = null;
                return true;
            } catch (error: any) {
                if (error.response?.status === 401 || error.response?.status === 403) {
                    this.stopPolling();
                    this.error = 'Ошибка доступа к тикету';
                } else if (error.response?.status === 404) {
                    this.stopPolling();
                    this.error = 'Тикет не найден';
                }
                
                return false;
            }
        },

        async showSystemNotification(type: 'message' | 'status') {
            if (!('Notification' in window)) return;
            
            if (Notification.permission === 'granted') {
                const title = type === 'status' ? 'Обновление статуса тикета' : 'Новое сообщение от поддержки';
                const body = type === 'status' 
                    ? `Статус вашего обращения изменен на: ${this.lastStatus}` 
                    : 'Администратор ответил на ваш вопрос в чате.';
                
                const notification = new Notification(title, {
                    body,
                    icon: '/favicon.ico',
                });

                notification.onclick = () => {
                    window.focus();
                    this.setModalVisible(true);
                };
            } else if (Notification.permission !== 'denied') {
                await Notification.requestPermission();
            }
        },

        triggerNotification(type: 'message' | 'status') {
            const audio = new Audio('/sounds/notification.mp3');
            audio.play().catch(e => console.warn('Audio play failed', e));
            
            window.dispatchEvent(new CustomEvent('support-notification', { detail: { type } }));
        },

        startPolling(email?: string) {
            this.stopPolling();
            this.pollingEmail = email;
            this.pollingInterval = 3000;
            
            this.fetchNew(email);
            this.pollingId = window.setInterval(() => this.fetchNew(email), this.pollingInterval);
            
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        setPollingInterval(interval: number) {
            if (this.pollingId) {
                clearInterval(this.pollingId);
                this.pollingInterval = interval;
                this.pollingId = window.setInterval(() => {
                    this.fetchNew(this.pollingEmail);
                }, this.pollingInterval);
            }
        },

        stopPolling() {
            if (this.pollingId) {
                clearInterval(this.pollingId);
                this.pollingId = null;
            }
        },

        setModalVisible(visible: boolean) {
            this.isModalVisible = visible;
            if (visible) {
                this.unreadCount = 0;
                localStorage.setItem('support_unread_count', '0');
            }
        },

        async telegramLink(): Promise<string | null> {
            if (!this.ticket) return null;
            try {
                const response = await axios.get(`/support/ticket/${this.ticket.id}/telegram-link`);
                return response.data?.telegram_link || null;
            } catch (error) {
                console.error('[SupportStore] telegram link error:', error);
                return null;
            }
        },

        reset() {
            this.stopPolling();
            this.ticket = null;
            this.lastMessageId = null;
            this.error = null;
            this.unreadCount = 0;
            this.lastStatus = null;
        }
    }
});
