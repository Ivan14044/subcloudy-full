import { defineStore } from 'pinia';
import axios from 'axios';
import { useLoadingStore } from '@/stores/loading';

interface CartItem {
    id: number;
    amount?: number;
    trial_amount?: number;
    [key: string]: any;
}

interface CartState {
    items: CartItem[];
    subscriptionTypes: Record<number, string>;
    promoFreeIds: number[];
    promoFreeAddedIds: number[];
    promoFreeDays: Record<number, number>;
}

export const useCartStore = defineStore('cart', {
    state: (): CartState => ({
        items: [],
        subscriptionTypes: {},
        promoFreeIds: [],
        promoFreeAddedIds: [],
        promoFreeDays: {}
    }),
    actions: {
        addToCart(service: CartItem, type = 'premium') {
            this.items.push(service);
            this.subscriptionTypes[service.id] = type;
        },
        async removeFromCart(id: number) {
            try {
                if (this.promoFreeIds.includes(id)) {
                    const { usePromoStore } = await import('@/stores/promo');
                    const promo = usePromoStore();
                    if (promo.code && promo.result && promo.result.type === 'free_access') {
                        return;
                    }
                }
            } catch (e) {
                // Ignore error
            }
            this.items = this.items.filter(item => item.id !== id);
            delete this.subscriptionTypes[id];
            this.promoFreeIds = this.promoFreeIds.filter(x => x !== id);
            this.promoFreeAddedIds = this.promoFreeAddedIds.filter(x => x !== id);
            delete this.promoFreeDays[id];
        },
        clearCart() {
            this.items = [];
            this.subscriptionTypes = {};
            this.promoFreeIds = [];
            this.promoFreeAddedIds = [];
            this.promoFreeDays = {};
        },
        setSubscriptionType(serviceId: number, type: string) {
            if (this.promoFreeIds.includes(serviceId)) return;
            this.subscriptionTypes[serviceId] = type;
        },
        async applyFreeAccessServices(services: any[]) {
            try {
                const { useServiceStore } = await import('@/stores/services');
                const serviceStore = useServiceStore();
                await serviceStore.fetchData();

                for (const s of services || []) {
                    const id = Number(s.id);
                    if (!this.items.some(item => item.id === id)) {
                        const svc = serviceStore.getById(id);
                        if (svc) {
                            this.items.push(svc);
                            this.subscriptionTypes[id] = 'premium';
                            this.promoFreeAddedIds.push(id);
                        }
                    }
                    this.subscriptionTypes[id] = 'premium';
                    if (!this.promoFreeIds.includes(id)) this.promoFreeIds.push(id);
                    if (typeof s.free_days !== 'undefined') {
                        this.promoFreeDays[id] = Number(s.free_days) || 0;
                    }
                }
            } catch (e) {
                console.error('applyFreeAccessServices error', e);
            }
        },
        removeFreeAccessServices() {
            for (const id of this.promoFreeIds) {
                delete this.promoFreeDays[id];
            }
            this.promoFreeIds = [];
            this.promoFreeAddedIds = [];
        },
        async submitCart({ paymentMethod, promocode }: { paymentMethod: string, promocode?: string }) {
            const loadingStore = useLoadingStore();
            loadingStore.start();

            try {
                const token = localStorage.getItem('token');
                const servicesData = this.items.map(item => ({
                    id: item.id,
                    subscription_type: this.subscriptionTypes[item.id] || 'trial'
                }));

                await axios.post(
                    '/cart',
                    {
                        services: servicesData,
                        payment_method: paymentMethod,
                        ...(promocode ? { promocode } : {})
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                );
                this.clearCart();
            } catch (error) {
                console.error('Failed to submit cart:', error);
                throw error;
            } finally {
                loadingStore.stop();
            }
        }
    },
    getters: {
        hasService: (state) => (id: number) => {
            return state.items.some(item => item.id === id);
        },
        getSubscriptionType: (state) => (serviceId: number) => {
            return state.subscriptionTypes[serviceId] || 'trial';
        },
        isFree: (state) => (id: number) => {
            return state.promoFreeIds.includes(id);
        },
        isPromoFreeAdded: (state) => (id: number) => {
            return state.promoFreeAddedIds.includes(id);
        },
        subtotalPaid: (state) => {
            return state.items.reduce((sum, item) => {
                if (state.promoFreeIds.includes(item.id)) return sum;
                const subscriptionType = state.subscriptionTypes[item.id] || 'trial';
                const price = subscriptionType === 'trial' ? item.trial_amount || 0 : item.amount || 0;
                return sum + price;
            }, 0);
        },
        totalAmount: (state) => {
            return state.items.reduce((sum, item) => {
                const subscriptionType = state.subscriptionTypes[item.id] || 'trial';
                const isFree = state.promoFreeIds.includes(item.id);
                const price = isFree
                    ? 0
                    : subscriptionType === 'trial'
                        ? item.trial_amount || 0
                        : item.amount || 0;
                return sum + price;
            }, 0);
        },
        totalTrialAmount: (state) => {
            return state.items.reduce((sum, item) => sum + (item.trial_amount || 0), 0);
        },
        totalPremiumAmount: (state) => {
            return state.items.reduce((sum, item) => sum + (item.amount || 0), 0);
        },
        trialServicesCount: (state) => {
            return Object.values(state.subscriptionTypes).filter(type => type === 'trial').length;
        },
        premiumServicesCount: (state) => {
            return Object.values(state.subscriptionTypes).filter(type => type === 'premium').length;
        }
    },
    // @ts-ignore
    persist: true
});


