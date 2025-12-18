<template>
    <div class="subscriptions-page">
        <!-- Header Section -->
        <div class="subscriptions-header">
            <h1 class="subscriptions-title">
                {{ $t('subscriptions.title') }}
            </h1>

            <div class="subscriptions-tabs">
                <button
                    class="subscriptions-tab"
                    :class="{ 'subscriptions-tab--active': activeTab === 'active' }"
                    @click="activeTab = 'active'"
                >
                    {{ $t('subscriptions.active_tab') }}
                </button>
                <button
                    class="subscriptions-tab"
                    :class="{ 'subscriptions-tab--active': activeTab === 'inactive' }"
                    @click="activeTab = 'inactive'"
                >
                    {{ $t('subscriptions.inactive_tab') }}
                </button>
            </div>
        </div>

        <div v-if="filteredSubscriptions.length" class="subscriptions-list">
            <div
                v-for="subscription in filteredSubscriptions"
                :key="subscription.id"
                class="subscription-item"
            >
                <div class="subscription-content">
                    <div class="subscription-info">
                        <h3
                            class="subscription-service-name"
                            :class="{ 'subscription-service-name--no-subtitle': getServiceSubtitle(subscription.service_id) === null }"
                            v-text="
                                $t('subscriptions.title_sub', {
                                    name: getServiceName(subscription.service_id)
                                })
                            "
                        ></h3>
                        <h4
                            v-if="getServiceSubtitle(subscription.service_id)"
                            class="subscription-service-subtitle"
                            v-text="getServiceSubtitle(subscription.service_id)"
                        ></h4>
                        <p
                            class="subscription-price"
                            v-html="
                                $t('plans.price_monthly', {
                                    price: getServiceAmount(subscription.service_id),
                                    currency: (serviceOption.options?.currency || 'USD').toUpperCase()
                                })
                            "
                        ></p>
                        <p class="subscription-meta">
                            {{
                                $t('subscriptions.payment_method', {
                                    method: $t('checkout.' + subscription.payment_method)
                                })
                            }}
                        </p>
                        <p class="subscription-meta">
                            {{
                                $t('subscriptions.created_at', {
                                    date: formatDate(subscription.created_at)
                                })
                            }}
                        </p>
                        <template
                            v-if="
                                subscription.payment_method === 'credit_card' &&
                                subscription.status === 'active'
                            "
                        >
                            <p
                                v-if="subscription.is_auto_renew"
                                class="subscription-meta"
                            >
                                {{
                                    $t('subscriptions.next_payment_at', {
                                        date: formatDate(subscription.next_payment_at)
                                    })
                                }}
                            </p>
                            <p
                                v-else
                                class="subscription-meta"
                                v-html="
                                    $t('subscriptions.end_at', {
                                        date: formatDate(subscription.next_payment_at)
                                    })
                                "
                            ></p>
                        </template>
                    </div>
                    <div class="subscription-logo-wrapper">
                        <img
                            :src="getServiceLogo(subscription.service_id)"
                            :alt="getServiceName(subscription.service_id)"
                            class="subscription-logo"
                        />
                    </div>
                </div>
                <div
                    v-if="subscription.status === 'active'"
                    class="subscription-actions"
                >
                    <div
                        :class="[
                            'subscription-status-wrapper',
                            subscription.status === 'active' &&
                            subscription.payment_method === 'credit_card'
                                ? 'subscription-status-wrapper--half'
                                : 'subscription-status-wrapper--full'
                        ]"
                    >
                        <span class="subscription-status-badge">
                            {{ $t('subscriptions.status_' + subscription.status) }}
                        </span>
                    </div>

                    <div
                        v-if="
                            subscription.status === 'active' &&
                            subscription.payment_method === 'credit_card'
                        "
                        class="subscription-action-wrapper"
                    >
                        <button
                            type="button"
                            class="subscription-action-button"
                            @click="
                                toggleAutoRenew(subscription.id, subscription.is_auto_renew)
                            "
                        >
                            {{
                                subscription.is_auto_renew
                                    ? $t('subscriptions.cancel')
                                    : $t('subscriptions.renew')
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <p
            v-if="!filteredSubscriptions.length"
            class="subscriptions-empty"
        >
            {{
                activeTab === 'active'
                    ? $t('subscriptions.empty_active')
                    : $t('subscriptions.empty_inactive')
            }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';
import { ref, computed, onMounted } from 'vue';
import { useServiceStore } from '@/stores/services';
import { useOptionStore } from '@/stores/options';
import { useAlert } from '@/utils/alert';
import { useTheme } from '@/composables/useTheme';

const authStore = useAuthStore();
const toast = useToast();
const { locale, t } = useI18n();
const { showConfirm } = useAlert();
const { isDark } = useTheme();

const activeTab = ref<'active' | 'inactive'>('active');
const serviceStore = useServiceStore();
const serviceOption = useOptionStore();

onMounted(() => {
    // Если подписок нет, попробуем обновить пользователя
    if (!authStore.user?.subscriptions?.length) {
        authStore.fetchUser();
    }
    
    // Если сервисы не загружены, загружаем их
    if (!serviceStore.services?.length) {
        serviceStore.fetchData();
    }
});

function getServiceName(serviceId) {
    const service = serviceStore.services.find(s => s.id === serviceId);
    return service?.translations?.[locale.value]?.name ?? `#${serviceId}`;
}

function getServiceSubtitle(serviceId) {
    const service = serviceStore.services.find(s => s.id === serviceId);
    return service?.translations?.[locale.value]?.subtitle ?? null;
}

function getServiceLogo(id) {
    const service = serviceStore.services.find(s => s.id === id);
    return service?.logo ?? '';
}

function getServiceAmount(id) {
    const service = serviceStore.services.find(s => s.id === id);
    return service?.amount.toFixed(2) ?? '';
}

const filteredSubscriptions = computed(() => {
    if (!authStore.user?.subscriptions?.length) return [];

    return authStore.user.subscriptions.filter(sub => {
        return activeTab.value === 'active' ? sub.status === 'active' : sub.status !== 'active';
    });
});

const formatDate = function (dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы с 0
    const year = date.getFullYear();

    return `${day}.${month}.${year}`;
};

const toggleAutoRenew = async function (id, isAutoRenew) {
    const isCancelling = isAutoRenew === 1;

    const res = await showConfirm({
        title: isCancelling
            ? t('subscriptions.toggle_confirm_title_cancel')
            : t('subscriptions.toggle_confirm_title_renew'),
        text: isCancelling
            ? t('subscriptions.toggle_confirm_text_cancel')
            : t('subscriptions.toggle_confirm_text_renew'),
        confirmText: t('subscriptions.toggle_confirm_yes'),
        cancelText: t('subscriptions.toggle_confirm_no')
    });

    if (res.isConfirmed) {
        try {
            await authStore.toggleAutoRenew(id);
            await authStore.fetchUser();
            toast.success(t('subscriptions.toggled_success'));
        } catch (e) {
            console.log(e);
            toast.error(t('subscriptions.toggled_error'));
        }
    }
};
</script>

<style scoped>
/* Основной контейнер страницы - явно устанавливаем позиционирование и z-index */
.subscriptions-page {
    width: 100%;
    max-width: 50%;
    margin: 0 auto;
    min-height: calc(100vh - 388px);
    padding: 4rem 1rem;
    position: relative;
    z-index: 1;
    opacity: 1 !important;
    filter: none !important;
    -webkit-filter: none !important;
    isolation: isolate; /* Создает новый stacking context */
}

@media (min-width: 640px) {
    .subscriptions-page {
        padding: 4rem 1.5rem;
    }
}

@media (min-width: 1024px) {
    .subscriptions-page {
        padding: 4rem 2rem;
    }
}

/* Заголовок страницы */
.subscriptions-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.subscriptions-title {
    font-size: 2.25rem;
    font-weight: 300;
    margin-top: 0.75rem;
    color: #111827;
}

.dark .subscriptions-title {
    color: #ffffff;
}

/* Контейнер вкладок */
.subscriptions-tabs {
    display: flex;
    justify-content: center;
    margin-top: 1.5rem;
    gap: 1rem;
}

/* Кнопки вкладок - полностью переписаны с нуля */
.subscriptions-tab {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border: none;
    outline: none;
    position: relative;
    z-index: 10;
    
    /* Явные цвета без прозрачности - принудительно */
    opacity: 1 !important;
    filter: none !important;
    -webkit-filter: none !important;
    will-change: auto;
    
    /* Неактивное состояние - светлая тема */
    background-color: #d1d5db !important;
    color: #1f2937 !important;
}

.subscriptions-tab:hover {
    background-color: #e5e7eb;
}

/* Активное состояние - светлая тема */
.subscriptions-tab--active {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
    cursor: default;
    opacity: 1 !important;
    filter: none !important;
}

.subscriptions-tab--active:hover {
    background-color: #3b82f6 !important;
    opacity: 1 !important;
}

/* Темная тема - неактивное состояние */
.dark .subscriptions-tab {
    background-color: #9ca3af !important;
    color: #1f2937 !important;
    opacity: 1 !important;
    filter: none !important;
}

.dark .subscriptions-tab:hover {
    background-color: #d1d5db !important;
    opacity: 1 !important;
}

/* Темная тема - активное состояние */
.dark .subscriptions-tab--active {
    background-color: #1e3a8a !important;
    color: #ffffff !important;
    opacity: 1 !important;
    filter: none !important;
}

.dark .subscriptions-tab--active:hover {
    background-color: #1e3a8a !important;
    opacity: 1 !important;
}

/* Список подписок */
.subscriptions-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Элемент подписки */
.subscription-item {
    border-bottom: 1px solid #9ca3af;
    padding-bottom: 0.625rem;
    opacity: 1;
}

/* Контент подписки */
.subscription-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Информация о подписке */
.subscription-info {
    flex: 1;
    min-width: 200px;
}

.subscription-service-name {
    font-size: 1.25rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.25rem;
}

.subscription-service-name--no-subtitle {
    margin-bottom: 0.25rem;
}

.dark .subscription-service-name {
    color: #ffffff;
}

.subscription-service-subtitle {
    font-size: 0.875rem;
    color: #4b5563;
    font-weight: 500;
    margin-bottom: 0.25rem;
    margin-top: 0;
    line-height: 1;
}

.dark .subscription-service-subtitle {
    color: #d1d5db;
}

.subscription-price {
    font-size: 0.875rem;
    color: #111827;
    line-height: 1.625;
}

.dark .subscription-price {
    color: #9ca3af;
}

.subscription-meta {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .subscription-meta {
    color: #9ca3af;
}

/* Обертка для логотипа */
.subscription-logo-wrapper {
    width: 124px;
    height: 100px;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1;
    filter: none;
    -webkit-filter: none;
}

/* Логотип сервиса - полностью переписан с нуля */
.subscription-logo {
    width: 100px;
    height: 100px;
    object-fit: contain;
    object-position: center;
    position: relative;
    z-index: 10;
    
    /* Явно убираем прозрачность - принудительно */
    opacity: 1 !important;
    filter: none !important;
    -webkit-filter: none !important;
    will-change: auto;
}

/* Действия подписки */
.subscription-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
    align-items: stretch;
    height: 2rem;
}

.subscription-status-wrapper {
    display: flex;
    align-items: stretch;
}

.subscription-status-wrapper--full {
    width: 100%;
}

.subscription-status-wrapper--half {
    width: 50%;
}

/* Бейдж статуса - полностью переписан с нуля */
.subscription-status-badge {
    display: inline-block;
    width: 100%;
    height: 100%;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 10;
    
    /* Явные цвета без прозрачности - принудительно */
    background-color: #4ade80 !important;
    color: #1f2937 !important;
    opacity: 1 !important;
    filter: none !important;
    -webkit-filter: none !important;
    will-change: auto;
}

.subscription-action-wrapper {
    width: 50%;
}

/* Кнопка действия */
.subscription-action-button {
    width: 100%;
    height: 100%;
    background-color: #000000 !important;
    color: #ffffff !important;
    border-radius: 0.5rem;
    border: none;
    outline: none;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    position: relative;
    z-index: 10;
    opacity: 1 !important;
    filter: none !important;
    will-change: auto;
}

.subscription-action-button:hover {
    background-color: #1f2937;
}

/* Пустое состояние */
.subscriptions-empty {
    text-align: center;
    color: #6b7280;
}

.dark .subscriptions-empty {
    color: #d1d5db;
}
</style>
