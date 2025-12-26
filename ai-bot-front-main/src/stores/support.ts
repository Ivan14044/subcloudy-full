import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

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
        pollingId: null as number | null,
        pollingInterval: 3000, // Текущий интервал polling в миллисекундах
        pollingEmail: null as string | null | undefined, // Email для polling
        lastMessageId: null as number | null,
        unreadCount: parseInt(localStorage.getItem('support_unread_count') || '0'),
        lastStatus: null as string | null,
    }),

    actions: {
        // Генерация уникального токена сессии для гостя
        generateSessionToken() {
            if (!this.sessionToken) {
                this.sessionToken = 'sess_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('support_session_token', this.sessionToken);
            }
            return this.sessionToken;
        },

        // Получение или создание тикета
        async ensureTicket(email?: string, isTelegramGuest: boolean = false): Promise<boolean> {
            this.loading = true;
            this.error = null;
            
            try {
                const auth = useAuthStore();
                const ticketIdToTry = localStorage.getItem('support_ticket_id');
                
                if (!this.sessionToken && !auth.isAuthenticated) {
                    this.generateSessionToken();
                }

                // Сбрасываем старые сообщения перед загрузкой нового тикета
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
                    // Для авторизованных пользователей используем POST для создания/получения тикета
                    response = await axios.post('/support/ticket', {
                        channel: 'web',
                        source: 'web'
                    });
                } else if (ticketIdToTry) {
                    // Пытаемся получить существующий тикет по ID для гостя
                    try {
                        response = await axios.get(`/support/ticket/${ticketIdToTry}`, {
                            params: baseParams
                        });
                    } catch (e) {
                        // Если по ID не нашли или доступ запрещен, пробуем создать/получить по email
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
                
                console.log('[SupportStore] ensureTicket: ticket received', {
                    ticketId: this.ticket?.id,
                    messagesCount: this.ticket?.messages?.length || 0,
                    messages: this.ticket?.messages,
                    ticketData: this.ticket
                });
                
                // Сохраняем данные для гостей для персистентности
                if (!auth.isAuthenticated) {
                    if (effectiveEmail) {
                        this.guestEmail = effectiveEmail;
                        localStorage.setItem('support_guest_email', effectiveEmail);
                    }
                    localStorage.setItem('support_ticket_id', String(this.ticket.id));
                }

                const msgs = this.ticket.messages || [];
                console.log('[SupportStore] ensureTicket: messages extracted', {
                    messagesArray: msgs,
                    messagesLength: msgs.length,
                    lastMessageId: msgs.length ? msgs[msgs.length - 1].id : null
                });
                
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

        // Получение новых сообщений
        async fetchNew(email?: string): Promise<boolean> {
            if (!this.ticket) {
                return false;
            }
            
            // Если страница скрыта, мы все равно делаем запрос, 
            // но браузер может его замедлять. Это нормально для получения уведомлений.
            
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
                    // Фильтруем дубликаты на всякий случай
                    const currentIds = new Set(this.ticket.messages.map(m => m.id));
                    const filtered = newMessages.filter(m => !currentIds.has(m.id));
                    
                    if (filtered.length > 0) {
                        this.ticket.messages.push(...filtered);
                        this.lastMessageId = filtered[filtered.length - 1].id;
                        
                        // Проверяем, есть ли сообщения от админа
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
                    
                    // Если страница скрыта или модалка закрыта - шлем системное уведомление
                    if (document.hidden || !this.isModalVisible) {
                        this.showSystemNotification(type);
                    }
                }

                // Очищаем ошибку при успешном запросе
                this.error = null;

                return true;
            } catch (error: any) {
                console.error('[SupportStore] polling error:', {
                    status: error.response?.status,
                    data: error.response?.data,
                    ticketId: this.ticket?.id
                });
                
                // Если ошибка авторизации или доступа - останавливаем поллинг
                if (error.response?.status === 401 || error.response?.status === 403) {
                    console.warn('[SupportStore] Stopping polling due to auth/access error');
                    this.stopPolling();
                    this.error = 'Ошибка доступа к тикету';
                } else if (error.response?.status === 404) {
                    console.warn('[SupportStore] Ticket not found, stopping polling');
                    this.stopPolling();
                    this.error = 'Тикет не найден';
                }
                
                return false;
            }
        },

        // Показать системное уведомление (Desktop Notification)
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
            // Проигрываем звук
            const audio = new Audio('/sounds/notification.mp3');
            audio.play().catch(e => console.warn('Audio play failed', e));
            
            // Генерируем событие для компонента (чтобы показать уведомление в чате)
            window.dispatchEvent(new CustomEvent('support-notification', { detail: { type } }));
        },

        // Запуск периодического опроса
        startPolling(email?: string) {
            this.stopPolling();
            this.pollingEmail = email;
            this.pollingInterval = 3000; // Быстрый интервал по умолчанию
            
            // Уменьшаем интервал до 3 секунд для более быстрой реакции
            this.fetchNew(email);
            this.pollingId = window.setInterval(() => this.fetchNew(email), this.pollingInterval);
            
            console.log('[SupportStore] startPolling', {
                email,
                interval: this.pollingInterval
            });
            
            // Запрашиваем разрешение на уведомления при старте чата
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        // Изменить интервал polling без остановки
        setPollingInterval(interval: number) {
            if (this.pollingId) {
                console.log('[SupportStore] setPollingInterval', {
                    oldInterval: this.pollingInterval,
                    newInterval: interval
                });
                
                // Останавливаем старый интервал
                clearInterval(this.pollingId);
                
                // Обновляем интервал
                this.pollingInterval = interval;
                
                // Запускаем новый интервал с новым временем
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
                // Оповещаем другие вкладки
                window.dispatchEvent(new StorageEvent('storage', {
                    key: 'support_unread_count',
                    newValue: '0'
                }));
            }
        },

        initExternalSync() {
            window.addEventListener('storage', (event) => {
                if (event.key === 'support_unread_count') {
                    this.unreadCount = parseInt(event.newValue || '0');
                }
                if (event.key === 'support_guest_email') {
                    this.guestEmail = event.newValue;
                }
            });
        },

        // Генерация ссылки для Telegram
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
