<template>
    <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8 position-relative z-1">
        <div
            :class="[
                'min-h-screen transition-colors duration-300 mt-3',
                isDark ? 'text-gray-100' : 'text-gray-900'
            ]"
        >
            <div class="fixed inset-0 pointer-events-none overflow-hidden">
                <div
                    :class="[
                        'animated-gradient absolute w-[120vw] h-[120vh]',
                        isDark ? 'opacity-60 blur-[80px]' : 'opacity-40 blur-[70px]'
                    ]"
                />
            </div>

            <div class="relative">
                <main>
                    <div class="max-w-7xl mx-auto mb-5 flex items-center justify-between">
                        <router-link
                            to="/"
                            class="flex items-center gap-2 text-dark dark:text-white hover:text-gray-400 transition-colors glass-button px-4 py-2 rounded-full"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>
                            {{ $t('services.page.back') }}
                        </router-link>
                    </div>

                    <div class="max-w-7xl mx-auto">
                        <div
                            v-if="service"
                            class="grid grid-cols-1 gap-8 items-start lg:grid-cols-12"
                        >
                            <section class="lg:col-span-5 xl:col-span-4">
                                <div
                                    :class="[
                                        'service-card',
                                        { 'service-card--coming-soon': isComingSoon }
                                    ]"
                                >
                                    <span
                                        v-if="isComingSoon"
                                        class="service-card__badge"
                                    >
                                        {{ $t('services.card.coming_soon') }}
                                    </span>

                                    <div class="service-card__logo">
                                        <picture>
                                            <source :srcset="optimizedLogo" type="image/webp" v-if="webPSupported" />
                                            <img
                                                :src="service.logo"
                                                :alt="`${getTranslation(service, 'name')} Logo`"
                                                width="140"
                                                height="140"
                                                loading="lazy"
                                                decoding="async"
                                                class="service-card__logo-img"
                                                style="aspect-ratio: 1 / 1;"
                                            />
                                        </picture>
                                    </div>

                                    <div class="service-card__title">
                                        <h2 class="service-card__name">
                                            {{ getTranslation(service, 'name') || service.name }}
                                        </h2>
                                        <p class="service-card__subtitle">
                                            {{ getTranslation(service, 'subtitle') }}
                                        </p>
                                    </div>

                                    <div class="service-card__price">
                                        <span class="service-card__amount">
                                            {{ service.amount.toFixed(2) }}
                                        </span>
                                        <span class="service-card__currency">
                                            {{ (serviceOption.options?.currency || 'USD').toUpperCase() }}
                                        </span>
                                    </div>

                                    <div class="service-card__actions">
                                        <template v-if="isComingSoon">
                                            <button
                                                class="service-card__btn service-card__btn--disabled"
                                                type="button"
                                                aria-disabled="true"
                                                @click.stop
                                            >
                                                {{ $t('services.card.coming_soon') }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button
                                                v-if="
                                                    isAuthenticated &&
                                                    authStore.user.active_services?.includes(service.id)
                                                "
                                                class="service-card__btn service-card__btn--success"
                                                type="button"
                                                @click="openService()"
                                            >
                                                {{ $t('plans.open') }}
                                            </button>

                                            <button
                                                v-else-if="!trialActivatedIds.includes(service.id)"
                                                class="service-card__btn"
                                                :class="[
                                                    isAdded
                                                        ? 'service-card__btn--success'
                                                        : 'service-card__btn--primary'
                                                ]"
                                                :aria-pressed="isAdded"
                                                aria-label="Add service"
                                                type="button"
                                                @click.stop="onAdd()"
                                            >
                                                <span v-if="!isAdded" class="service-card__btn-content">
                                                    {{ $t('plans.add_to_cart') }}
                                                    <svg
                                                        class="service-card__btn-icon"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                        />
                                                    </svg>
                                                </span>
                                                <span v-else>
                                                    <template v-if="addedState === 'check'">✓</template>
                                                    <template v-else>{{
                                                        $t('plans.go_to_checkout')
                                                    }}</template>
                                                </span>
                                            </button>

                                            <button
                                                v-if="canShowTrialButton"
                                                class="service-card__btn service-card__btn--outline"
                                                type="button"
                                                @click="tryTrial()"
                                            >
                                                {{ $t('plans.get_trial') }}
                                            </button>

                                            <button
                                                v-if="trialActivatedIds.includes(service.id)"
                                                class="service-card__btn service-card__btn--success"
                                                type="button"
                                                @click="goToCheckout()"
                                            >
                                                {{ $t('plans.go_to_checkout') }}
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </section>

                            <section class="lg:col-span-7 xl:col-span-8">
                                <div class="service-description">
                                    <h1 class="service-description__heading">
                                        {{ getTranslation(service, 'name') }}
                                    </h1>
                                    <div
                                        class="service-description__body service-content"
                                        v-html="getTranslation(service, 'full_description')"
                                    ></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import { useServiceStore } from '@/stores/services';
import { useCartStore } from '@/stores/cart';
import { useAuthStore } from '@/stores/auth';
import { useOptionStore } from '@/stores/options';
import { useWebP } from '@/composables/useWebP';
import { updateWebPageSEO } from '@/utils/seo';

interface Service {
    id: number;
    logo: string;
    amount: number;
    [key: string]: any;
}

const { locale } = useI18n();
const route = useRoute();
const router = useRouter();
const { isDark } = useTheme();
const serviceStore = useServiceStore();
const cartStore = useCartStore();
const authStore = useAuthStore();
const serviceOption = useOptionStore();
const service = ref<Service | null>(null);
const trialActivatedIds = ref<number[]>([]);
const { webPSupported, getOptimizedUrl } = useWebP();

const optimizedLogo = computed(() => {
    return service.value ? getOptimizedUrl(service.value.logo) : '';
});
const isAuthenticated = computed(() => !!authStore.user);
const addedState = ref<'check' | 'checkout'>('check');
const isAdded = ref(false);

const isComingSoon = computed(() => {
    const raw = service.value?.available_accounts;
    if (raw === null || raw === undefined) return false;
    const value = typeof raw === 'number' ? raw : Number(raw);
    if (Number.isNaN(value)) return false;
    return value <= 0;
});

const openService = async () => {
    const current = service.value;
    if (!current) return;

    // Перенаправляем на страницу с информацией о необходимости скачать приложение
    router.push({
        path: '/download-app',
        query: { serviceId: current.id }
    });
};

const canShowTrialButton = computed(() => {
    const current = service.value;
    if (!current) return false;
    if (isComingSoon.value) return false;
    return (
        !trialActivatedIds.value.includes(current.id) &&
        current.trial_amount &&
        (!isAuthenticated.value || !authStore.user.active_services?.includes(current.id)) &&
        cartStore.subscriptionTypes[current.id] !== 'trial'
    );
});

const getTranslation = (service, key) => {
    return service.translations[locale.value]?.[key] ?? service.translations['en']?.[key] ?? null;
};

const tryTrial = () => {
    const current = service.value;
    if (!current) return;
    const serviceId = current.id;

    if (cartStore.hasService(serviceId) && cartStore.subscriptionTypes[serviceId] === 'premium') {
        cartStore.removeFromCart(serviceId);
    }

    if (!trialActivatedIds.value.includes(serviceId)) {
        trialActivatedIds.value.push(serviceId);
    }

    isAdded.value = true;
    cartStore.addToCart(current, 'trial');
};

const onAdd = () => {
    const current = service.value;
    if (!current) return;

    if (cartStore.hasService(current.id)) {
        goToCheckout();
    } else {
        isAdded.value = true;

        addedState.value = 'check';
        setTimeout(() => {
            addedState.value = 'checkout';
        }, 1000);

        cartStore.addToCart(current, 'premium');
    }
};

const goToCheckout = () => {
    if (isAuthenticated.value) {
        router.push('/checkout');
    } else {
        router.push({
            path: '/login',
            query: { redirect: 'checkout' }
        });
    }
};

onMounted(async () => {
    const serviceId = Number(route.params.id);

    // Загружаем опции, если они еще не загружены
    if (!serviceOption.isLoaded) {
        await serviceOption.fetchData();
    }

    if (!serviceStore.isLoaded) {
        await serviceStore.fetchData();
    }

    // Пробуем найти сервис по ID (как число и как строку)
    service.value = serviceStore.getById(serviceId) || serviceStore.getById(Number(serviceId));

    if (!service.value) {
        console.error('Service not found:', serviceId, 'Available services:', serviceStore.services.map(s => s.id));
        await router.replace('/404');
        return;
    }

    const current = service.value;
    if (!current) return;

    updateServiceSEO();

    isAdded.value = cartStore.hasService(current.id);
    if (isAdded.value) {
        addedState.value = 'checkout';
    }
});

const updateServiceSEO = () => {
    const s = service.value;
    if (!s) return;

    const name = getTranslation(s, 'name') || s.name;
    const subtitle = getTranslation(s, 'subtitle') || '';
    const description = getTranslation(s, 'short_description_card') || subtitle;

    updateWebPageSEO({
        title: `${name} — Подписка и оплата | SubCloudy`,
        description: description,
        canonical: `/service/${s.id}`,
        image: s.logo,
        locale: locale.value
    });
};

watch(locale, () => {
    updateServiceSEO();
});

watch(
    () => cartStore.services,
    () => {
        const currentId = service.value?.id;
        if (currentId) {
            isAdded.value = cartStore.hasService(currentId);
        } else {
            isAdded.value = false;
        }
    },
    { deep: true }
);
</script>

<style scoped>
.service-content ::v-deep(p) {
    padding-bottom: 15px;
}

.service-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 24px;
    padding: 42px 36px;
    background: linear-gradient(155deg, rgba(255, 255, 255, 0.52), rgba(255, 255, 255, 0.22));
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 28px;
    box-shadow: 0 24px 48px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(24px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
}

.dark .service-card {
    background: linear-gradient(165deg, rgba(21, 32, 62, 0.82), rgba(17, 24, 39, 0.68));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 28px 60px rgba(2, 6, 23, 0.45);
}

.service-card__badge {
    position: absolute;
    top: 22px;
    right: 22px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.4px;
    background: rgba(255, 255, 255, 0.35);
    color: #1f2937;
    border: 1px solid rgba(15, 23, 42, 0.08);
    backdrop-filter: blur(10px);
}

.dark .service-card__badge {
    background: rgba(17, 24, 39, 0.5);
    color: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.16);
}

.service-card__logo {
    width: 112px;
    height: 112px;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}

.service-card__logo-img {
    width: 72px;
    height: 72px;
    object-fit: contain;
}

.service-card__title {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.service-card__name {
    font-size: 32px;
    font-weight: 700;
    color: #0f172a;
}

.dark .service-card__name {
    color: #f8fafc;
}

.service-card__subtitle {
    font-size: 15px;
    color: rgba(15, 23, 42, 0.7);
}

.dark .service-card__subtitle {
    color: rgba(226, 232, 240, 0.7);
}

.service-card__price {
    display: flex;
    align-items: baseline;
    gap: 8px;
    font-weight: 700;
}

.service-card__amount {
    font-size: 28px;
    color: #0f172a;
}

.service-card__currency {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: rgba(15, 23, 42, 0.6);
}

.dark .service-card__amount {
    color: #f8fafc;
}

.dark .service-card__currency {
    color: rgba(226, 232, 240, 0.6);
}

.service-card__actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: min(100%, 320px);
}

.service-card__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 20px;
    border-radius: 16px;
    font-size: 15px;
    font-weight: 600;
    line-height: 1.2;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    border: none;
}

.service-card__btn-content {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.service-card__btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.service-card__btn--primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #ffffff;
    box-shadow: 0 14px 28px rgba(37, 99, 235, 0.2);
}

.service-card__btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 32px rgba(37, 99, 235, 0.25);
}

.service-card__btn--success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 14px 28px rgba(16, 185, 129, 0.22);
}

.service-card__btn--success:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 32px rgba(5, 150, 105, 0.28);
}

.service-card__btn--outline {
    background: transparent;
    border: 1px solid rgba(99, 102, 241, 0.85);
    color: rgba(99, 102, 241, 0.95);
}

.dark .service-card__btn--outline {
    border-color: rgba(129, 140, 248, 0.8);
    color: rgba(196, 203, 255, 0.9);
}

.service-card__btn--outline:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
}

.service-card__btn--disabled {
    background: rgba(107, 114, 128, 0.25);
    color: rgba(31, 41, 55, 0.7);
    cursor: not-allowed;
}

.dark .service-card__btn--disabled {
    background: rgba(55, 65, 81, 0.55);
    color: rgba(255, 255, 255, 0.75);
}

.service-card__btn-icon {
    width: 16px;
    height: 16px;
}

.service-card--coming-soon,
.service-card--coming-soon .service-card__subtitle,
.service-card--coming-soon .service-card__amount,
.service-card--coming-soon .service-card__currency,
.service-card--coming-soon .service-card__name {
    color: rgba(107, 114, 128, 0.78) !important;
}

.service-card--coming-soon .service-card__logo {
    background: transparent;
    box-shadow: none;
}

.service-card--coming-soon .service-card__logo-img {
    filter: grayscale(1);
    opacity: 0.75;
}

.service-description {
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.1));
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 32px;
    padding: 48px;
    box-shadow: 0 20px 42px rgba(15, 23, 42, 0.1);
    backdrop-filter: blur(18px);
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.dark .service-description {
    background: linear-gradient(165deg, rgba(17, 24, 39, 0.7), rgba(15, 23, 42, 0.6));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 24px 54px rgba(2, 6, 23, 0.45);
}

.service-description__heading {
    font-size: 34px;
    font-weight: 800;
    color: #0f172a;
}

.dark .service-description__heading {
    color: #f8fafc;
}

.service-description__body {
    color: rgba(15, 23, 42, 0.85);
    font-size: 16px;
    line-height: 1.7;
}

.dark .service-description__body {
    color: rgba(226, 232, 240, 0.85);
}

.animated-gradient {
    background: linear-gradient(
        120deg,
        rgba(255, 106, 0, 0.35) 10%,
        rgba(255, 0, 204, 0.55) 35%,
        rgba(0, 170, 255, 0.75) 70%,
        rgba(0, 123, 255, 0.45) 90%
    );
    animation: gradientMove 30s ease-in-out infinite;
}

@keyframes gradientMove {
    0%,
    100% {
        transform: translate(-18%, -18%) rotate(0deg) scale(1);
    }
    25% {
        transform: translate(-10%, -22%) rotate(20deg) scale(1.03);
    }
    50% {
        transform: translate(8%, -12%) rotate(40deg) scale(0.98);
    }
    75% {
        transform: translate(-12%, 8%) rotate(25deg) scale(1.02);
    }
}

@media (max-width: 1280px) {
    .service-card {
        padding: 38px 30px;
    }
}

@media (max-width: 1024px) {
    .service-card {
        padding: 34px 28px;
    }
    .service-card__name {
        font-size: 28px;
    }
    .service-card__actions {
        width: 100%;
    }
    .service-description {
        padding: 36px;
    }
}

@media (max-width: 768px) {
    .service-card {
        padding: 32px 24px;
    }
    .service-description {
        padding: 32px 24px;
    }
}

@media (max-width: 640px) {
    main {
        padding-left: 12px;
        padding-right: 12px;
    }
    .service-card {
        padding: 28px 22px;
        border-radius: 24px;
    }
    .service-card__logo {
        width: 96px;
        height: 96px;
    }
    .service-card__logo-img {
        width: 64px;
        height: 64px;
    }
    .service-description {
        border-radius: 26px;
        padding: 28px 22px;
    }
    .service-description__heading {
        font-size: 28px;
    }
}
</style>
