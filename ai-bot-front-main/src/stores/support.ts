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
    subject: string;
    messages: TicketMessage[];
}

export const useSupportStore = defineStore('support', {
    state: () => ({
        ticket: null as Ticket | null,
        loading: false,
        error: null as string | null,
        pollingInterval: null as number | null,
    }),

    getters: {
        hasTicket: (state) => !!state.ticket,
        lastMessageId: (state) => {
            if (!state.ticket || !state.ticket.messages.length) return null;
            return Math.max(...state.ticket.messages.map(m => m.id));
        },
    },

    actions: {
        async getOrCreateTicket(email?: string) {
            this.loading = true;
            this.error = null;

            try {
                const authStore = useAuthStore();
                const config: any = {};

                // Если пользователь не авторизован, передаем email
                if (!authStore.isAuthenticated && email) {
                    config.params = { email };
                }

                const response = await axios.get('/api/support/ticket', config);
                
                if (response.data.success) {
                    this.ticket = response.data.ticket;
                    return this.ticket;
                } else {
                    throw new Error(response.data.error || 'Failed to get ticket');
                }
            } catch (error: any) {
                this.error = error.response?.data?.error || error.message || 'Ошибка при загрузке обращения';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async sendMessage(text: string, source: 'web' | 'telegram' = 'web', email?: string) {
            if (!this.ticket) {
                throw new Error('No ticket available');
            }

            this.loading = true;
            this.error = null;

            try {
                const authStore = useAuthStore();
                const config: any = {
                    text,
                    source,
                };

                // Если пользователь не авторизован, передаем email
                if (!authStore.isAuthenticated && email) {
                    config.email = email;
                }

                const response = await axios.post(
                    `/api/support/ticket/${this.ticket.id}/message`,
                    config
                );

                if (response.data.success) {
                    // Добавляем новое сообщение в список
                    if (this.ticket) {
                        this.ticket.messages.push(response.data.message);
                    }
                    return response.data.message;
                } else {
                    throw new Error(response.data.error || 'Failed to send message');
                }
            } catch (error: any) {
                this.error = error.response?.data?.error || error.message || 'Ошибка при отправке сообщения';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async getNewMessages(email?: string) {
            if (!this.ticket) {
                return;
            }

            try {
                const authStore = useAuthStore();
                const config: any = {};

                if (this.lastMessageId) {
                    config.params = { last_message_id: this.lastMessageId };
                }

                // Если пользователь не авторизован, передаем email
                if (!authStore.isAuthenticated && email) {
                    if (config.params) {
                        config.params.email = email;
                    } else {
                        config.params = { email };
                    }
                }

                const response = await axios.get(
                    `/api/support/ticket/${this.ticket.id}/messages`,
                    config
                );

                if (response.data.success && response.data.messages.length > 0) {
                    // Добавляем новые сообщения
                    if (this.ticket) {
                        this.ticket.messages.push(...response.data.messages);
                    }
                }
            } catch (error: any) {
                console.error('Error fetching new messages:', error);
            }
        },

        async getTelegramLink() {
            if (!this.ticket) {
                throw new Error('No ticket available');
            }

            try {
                const response = await axios.get(
                    `/api/support/ticket/${this.ticket.id}/telegram-link`
                );

                if (response.data.success) {
                    return response.data.telegram_link;
                } else {
                    throw new Error(response.data.error || 'Failed to get Telegram link');
                }
            } catch (error: any) {
                this.error = error.response?.data?.error || error.message || 'Ошибка при получении ссылки';
                throw error;
            }
        },

        startPolling(email?: string) {
            this.stopPolling();
            
            // Polling каждые 3 секунды
            this.pollingInterval = window.setInterval(() => {
                this.getNewMessages(email);
            }, 3000);
        },

        stopPolling() {
            if (this.pollingInterval !== null) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },

        reset() {
            this.ticket = null;
            this.error = null;
            this.stopPolling();
        },
    },
});

