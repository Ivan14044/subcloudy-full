import { defineStore } from 'pinia';
import axios from 'axios';
import i18n from '@/i18n';
import { useLoadingStore } from '@/stores/loading';

(() => {
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`;
    }
})();

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: (() => {
            const raw = localStorage.getItem('user');
            try {
                return raw ? JSON.parse(raw) : null;
            } catch {
                return null;
            }
        })(),
        token: localStorage.getItem('token') || '',
        errors: {},
        userLoaded: false
    }),

    getters: {
        isAuthenticated: state => !!state.token && !!state.user,
        hasSession: state => !!state.token
    },

    actions: {
        async init() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                try {
                    await this.fetchUser();
                } finally {
                    this.userLoaded = true;
                }
            } else {
                this.userLoaded = true;
            }
        },

        async register(formData: any) {
            const loadingStore = useLoadingStore();
            this.errors = {};
            loadingStore.start();

            try {
                const lang = i18n.global.locale.value;
                const response = await axios.post('/register', { ...formData, lang });

                this.token = response.data.token;
                this.user = response.data.user;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                this.userLoaded = true;
                return true;
            } catch (error: any) {
                if (
                    error.response?.data?.errors &&
                    typeof error.response.data.errors === 'object'
                ) {
                    this.errors = error.response.data.errors;
                }
                return false;
            } finally {
                loadingStore.stop();
            }
        },

        async login(formData: any) {
            const loadingStore = useLoadingStore();
            this.errors = {};
            loadingStore.start();

            try {
                const response = await axios.post('/login', formData);

                this.token = response.data.token;
                this.user = response.data.user;

                localStorage.setItem('token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                const userLang = this.user?.lang ?? null;
                if (userLang) {
                    i18n.global.locale.value = userLang;
                    localStorage.setItem('user-language', userLang);
                }

                this.userLoaded = true;
                return true;
            } catch (error: any) {
                if (
                    error.response?.data?.errors &&
                    typeof error.response.data.errors === 'object'
                ) {
                    this.errors = error.response.data.errors;
                }
                return false;
            } finally {
                loadingStore.stop();
            }
        },

        async forgotPassword(formData: any) {
            const loadingStore = useLoadingStore();
            this.errors = {};
            loadingStore.start();

            try {
                await axios.post('/forgot-password', formData);
                return true;
            } catch (error: any) {
                if (
                    error.response?.data?.errors &&
                    typeof error.response.data.errors === 'object'
                ) {
                    this.errors = error.response.data.errors;
                }
                return false;
            } finally {
                loadingStore.stop();
            }
        },

        async resetPassword(formData: any) {
            const loadingStore = useLoadingStore();
            this.errors = {};
            loadingStore.start();

            try {
                await axios.post('/reset-password', formData);
                return true;
            } catch (error: any) {
                if (
                    error.response?.data?.errors &&
                    typeof error.response.data.errors === 'object'
                ) {
                    this.errors = error.response.data.errors;
                }
                return false;
            } finally {
                loadingStore.stop();
            }
        },

        async logout() {
            try {
                await axios.get('/logout', {
                    headers: { Authorization: `Bearer ${this.token}` }
                });
            } catch (error) {
                console.error(error);
            } finally {
                this.token = '';
                this.user = null;
                this.userLoaded = true;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        async fetchUser() {
            if (!this.token) {
                this.userLoaded = true;
                return;
            }
            try {
                // #region agent log
                const beforeFetchData = {
                    location: 'auth.ts:fetchUser',
                    message: 'Before fetching user',
                    data: { hasToken: !!this.token, tokenLength: this.token.length },
                    timestamp: Date.now(),
                    sessionId: 'debug-session',
                    runId: 'run1',
                    hypothesisId: 'E'
                };
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(beforeFetchData)}).catch(()=>{});
                // #endregion
                
                const response = await axios.get('/user', {
                    headers: { Authorization: `Bearer ${this.token}` }
                });
                
                // #region agent log
                const afterFetchData = {
                    location: 'auth.ts:fetchUser',
                    message: 'User fetched from API',
                    data: {
                        hasResponseData: !!response.data,
                        responseDataKeys: response.data ? Object.keys(response.data) : [],
                        hasSubscriptions: !!response.data?.subscriptions,
                        subscriptionsLength: response.data?.subscriptions?.length || 0,
                        subscriptions: response.data?.subscriptions || [],
                        responseDataType: typeof response.data
                    },
                    timestamp: Date.now(),
                    sessionId: 'debug-session',
                    runId: 'run1',
                    hypothesisId: 'E'
                };
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(afterFetchData)}).catch(()=>{});
                // #endregion
                
                this.user = response.data;
                localStorage.setItem('user', JSON.stringify(this.user));
                
                // #region agent log
                const afterSetData = {
                    location: 'auth.ts:fetchUser',
                    message: 'User set in store',
                    data: {
                        hasUser: !!this.user,
                        hasSubscriptions: !!this.user?.subscriptions,
                        subscriptionsLength: this.user?.subscriptions?.length || 0,
                        userKeys: this.user ? Object.keys(this.user) : []
                    },
                    timestamp: Date.now(),
                    sessionId: 'debug-session',
                    runId: 'run1',
                    hypothesisId: 'E'
                };
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(afterSetData)}).catch(()=>{});
                // #endregion
            } catch (error) {
                // #region agent log
                const errorData = {
                    location: 'auth.ts:fetchUser',
                    message: 'Error fetching user',
                    data: {
                        error: error instanceof Error ? error.message : String(error),
                        hasResponse: !!(error as any)?.response,
                        status: (error as any)?.response?.status,
                        responseData: (error as any)?.response?.data
                    },
                    timestamp: Date.now(),
                    sessionId: 'debug-session',
                    runId: 'run1',
                    hypothesisId: 'E'
                };
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(errorData)}).catch(()=>{});
                // #endregion
                console.log(error);
                await this.logout();
            } finally {
                this.userLoaded = true;
            }
        },

        async cancelSubscription(id: number) {
            if (!this.token) return;
            const loadingStore = useLoadingStore();
            loadingStore.start();
            try {
                await axios.post(
                    '/cancel-subscription',
                    { subscription_id: id },
                    {
                        headers: { Authorization: `Bearer ${this.token}` }
                    }
                );
            } catch (error) {
                console.error(error);
            } finally {
                loadingStore.stop();
            }
        },

        async toggleAutoRenew(id: number) {
            if (!this.token) return;
            const loadingStore = useLoadingStore();
            loadingStore.start();
            try {
                await axios.post(
                    '/toggle-auto-renew',
                    { subscription_id: id },
                    {
                        headers: { Authorization: `Bearer ${this.token}` }
                    }
                );
            } catch (error) {
                console.error(error);
                throw error;
            } finally {
                loadingStore.stop();
            }
        },

        async update(formData: any) {
            if (!this.token) return;

            const isOnlyLang = Object.keys(formData).length === 1 && 'lang' in formData;
            this.errors = {};
            const loadingStore = useLoadingStore();
            if (!isOnlyLang) loadingStore.start();

            // Сохраняем подписки и active_services перед обновлением
            const savedSubscriptions = this.user?.subscriptions;
            const savedActiveServices = this.user?.active_services;
            // #region agent log
            fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'auth.ts:update',message:'Before update - saving subscriptions',data:{hasSavedSubscriptions:!!savedSubscriptions,savedSubscriptionsLength:savedSubscriptions?.length || 0,hasSavedActiveServices:!!savedActiveServices,formData},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
            // #endregion

            try {
                const response = await axios.post('/user', formData, {
                    headers: { Authorization: `Bearer ${this.token}` }
                });
                // #region agent log
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'auth.ts:update',message:'After API response',data:{hasResponseUser:!!response.data.user,hasSubscriptionsInResponse:!!response.data.user?.subscriptions,subscriptionsLengthInResponse:response.data.user?.subscriptions?.length || 0,hasActiveServicesInResponse:!!response.data.user?.active_services},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
                // #endregion
                this.user = response.data.user;
                
                // Восстанавливаем подписки и active_services, если их нет в ответе
                if (!this.user.subscriptions && savedSubscriptions) {
                    this.user.subscriptions = savedSubscriptions;
                    // #region agent log
                    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'auth.ts:update',message:'Restored subscriptions',data:{restoredLength:savedSubscriptions.length},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
                    // #endregion
                }
                if (!this.user.active_services && savedActiveServices) {
                    this.user.active_services = savedActiveServices;
                    // #region agent log
                    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'auth.ts:update',message:'Restored active_services',data:{restoredLength:savedActiveServices.length},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
                    // #endregion
                }
                // #region agent log
                fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'auth.ts:update',message:'After restore - final state',data:{hasSubscriptions:!!this.user.subscriptions,subscriptionsLength:this.user.subscriptions?.length || 0,hasActiveServices:!!this.user.active_services},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'J'})}).catch(()=>{});
                // #endregion
                localStorage.setItem('user', JSON.stringify(this.user));
                return true;
            } catch (error: any) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                }
                return false;
            } finally {
                if (!isOnlyLang) loadingStore.stop();
            }
        },

        setToken(token: string) {
            this.token = token;
            localStorage.setItem('token', token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },

        setUser(user: any) {
            this.user = user;
            localStorage.setItem('user', JSON.stringify(user));
        }
    }
});
