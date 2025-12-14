<template>
    <div class="w-full lg:w-1/2 mx-auto min-h-[calc(100vh-388px)] px-4 py-16 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-light text-gray-900 dark:text-white mt-3">
                {{ $t('subscriptions.title') }}
            </h1>

            <div class="flex justify-center mt-6 space-x-4">
                <button
                    :class="[
                        'px-4 py-2 rounded-lg font-medium',
                        activeTab === 'active'
                            ? 'bg-blue-500 dark:bg-blue-900 text-white cursor-default'
                            : 'bg-gray-300 dark:bg-gray-400 dark:text-gray-800 text-gray-800 hover:!bg-gray-200'
                    ]"
                    @click="activeTab = 'active'"
                >
                    {{ $t('subscriptions.active_tab') }}
                </button>
                <button
                    :class="[
                        'px-4 py-2 rounded-lg font-medium',
                        activeTab === 'inactive'
                            ? 'bg-blue-500 dark:bg-blue-900 text-white cursor-default'
                            : 'bg-gray-300 dark:bg-gray-400 dark:text-gray-800 text-gray-800 hover:!bg-gray-200'
                    ]"
                    @click="activeTab = 'inactive'"
                >
                    {{ $t('subscriptions.inactive_tab') }}
                </button>
            </div>
        </div>

        <div v-if="filteredSubscriptions.length" class="space-y-6">
            <div
                v-for="subscription in filteredSubscriptions"
                :key="subscription.id"
                class="space-y-2 mb-2"
            >
                <div class="d-flex align-center gap-3 flex-wrap service-item">
                    <div class="flex-1 min-w-[200px]">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3
                                    class="text-xl font-medium text-gray-900 dark:!text-white"
                                    :class="{
                                        'mb-1': getServiceSubtitle(subscription.service_id) === null
                                    }"
                                    v-text="
                                        $t('subscriptions.title_sub', {
                                            name: getServiceName(subscription.service_id)
                                        })
                                    "
                                ></h3>
                                <h4
                                    v-if="getServiceSubtitle(subscription.service_id)"
                                    class="text-sm text-gray-600 dark:!text-gray-300 font-medium mb-1 mt-0 lh-1"
                                    v-text="getServiceSubtitle(subscription.service_id)"
                                ></h4>
                                <p
                                    class="text-sm text-gray-900 dark:!text-gray-400 leading-relaxed"
                                    v-html="
                                        $t('plans.price_monthly', {
                                            price: getServiceAmount(subscription.service_id),
                                            currency: (serviceOption.options?.currency || 'USD').toUpperCase()
                                        })
                                    "
                                ></p>
                                <p class="text-[12px] dark:!text-gray-400">
                                    {{
                                        $t('subscriptions.payment_method', {
                                            method: $t('checkout.' + subscription.payment_method)
                                        })
                                    }}
                                </p>
                                <p class="text-[12px] dark:!text-gray-400">
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
                                        class="text-[12px] dark:!text-gray-400"
                                    >
                                        {{
                                            $t('subscriptions.next_payment_at', {
                                                date: formatDate(subscription.next_payment_at)
                                            })
                                        }}
                                    </p>
                                    <p
                                        v-else
                                        class="text-[12px] dark:!text-gray-400"
                                        v-html="
                                            $t('subscriptions.end_at', {
                                                date: formatDate(subscription.next_payment_at)
                                            })
                                        "
                                    ></p>
                                </template>
                            </div>
                            <img
                                :src="getServiceLogo(subscription.service_id)"
                                :alt="getServiceName(subscription.service_id)"
                                class="w-[100px] h-[100px] object-contain object-center service-logo"
                            />
                        </div>
                        <div
                            v-if="subscription.status === 'active'"
                            class="flex gap-2 mt-2 items-stretch h-8"
                        >
                            <div
                                :class="[
                                    subscription.status !== 'active' ||
                                    subscription.payment_method !== 'credit_card'
                                        ? 'w-full'
                                        : 'w-1/2'
                                ]"
                            >
                                <span
                                    class="inline-block w-full h-full text-xs font-medium px-2 py-1 rounded-lg text-center flex items-center justify-center bg-green-400 text-gray-800"
                                >
                                    {{ $t('subscriptions.status_' + subscription.status) }}
                                </span>
                            </div>

                            <div
                                v-if="
                                    subscription.status === 'active' &&
                                    subscription.payment_method === 'credit_card'
                                "
                                class="w-1/2"
                            >
                                <button
                                    type="button"
                                    class="w-full h-full bg-black text-white rounded-lg hover:bg-gray-800 text-sm"
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
            </div>
        </div>
        <p
            v-if="!filteredSubscriptions.length"
            class="text-center text-gray-500 dark:text-gray-300"
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
import { ref, computed, onMounted, watch } from 'vue';
import { useServiceStore } from '@/stores/services';
import { useOptionStore } from '@/stores/options';
import { useAlert } from '@/utils/alert';

const authStore = useAuthStore();
const toast = useToast();
const { locale, t } = useI18n();
const { showConfirm } = useAlert();

const activeTab = ref<'active' | 'inactive'>('active');
const serviceStore = useServiceStore();
const serviceOption = useOptionStore();

// #region agent log
onMounted(() => {
    const logData = {
        location: 'SubscriptionsPage.vue:onMounted',
        message: 'SubscriptionsPage mounted',
        data: {
            hasUser: !!authStore.user,
            hasSubscriptions: !!authStore.user?.subscriptions,
            subscriptionsLength: authStore.user?.subscriptions?.length || 0,
            subscriptions: authStore.user?.subscriptions || [],
            userKeys: authStore.user ? Object.keys(authStore.user) : [],
            hasServices: !!serviceStore.services,
            servicesLength: serviceStore.services?.length || 0,
            filteredSubscriptionsLength: filteredSubscriptions.value.length,
            filteredSubscriptions: filteredSubscriptions.value
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'A'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(logData)}).catch(()=>{});
    
    // Если подписок нет, попробуем обновить пользователя
    if (!authStore.user?.subscriptions?.length) {
        fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:onMounted',message:'No subscriptions found, fetching user',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
        authStore.fetchUser().then(() => {
            const afterFetchData = {
                location: 'SubscriptionsPage.vue:afterFetchUser',
                message: 'User fetched after mount',
                data: {
                    hasSubscriptions: !!authStore.user?.subscriptions,
                    subscriptionsLength: authStore.user?.subscriptions?.length || 0,
                    subscriptions: authStore.user?.subscriptions || [],
                    filteredSubscriptionsLength: filteredSubscriptions.value.length
                },
                timestamp: Date.now(),
                sessionId: 'debug-session',
                runId: 'run1',
                hypothesisId: 'B'
            };
            fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(afterFetchData)}).catch(()=>{});
        });
    }
    
    // Если сервисы не загружены, загружаем их
    if (!serviceStore.services?.length) {
        fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:onMounted',message:'No services found, fetching services',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'F'})}).catch(()=>{});
        serviceStore.fetchData().then(() => {
            const afterFetchServicesData = {
                location: 'SubscriptionsPage.vue:afterFetchServices',
                message: 'Services fetched after mount',
                data: {
                    servicesLength: serviceStore.services?.length || 0,
                    services: serviceStore.services || []
                },
                timestamp: Date.now(),
                sessionId: 'debug-session',
                runId: 'run1',
                hypothesisId: 'F'
            };
            fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(afterFetchServicesData)}).catch(()=>{});
        });
    }
});

watch(() => authStore.user?.subscriptions, (newSubs, oldSubs) => {
    const logData = {
        location: 'SubscriptionsPage.vue:watch',
        message: 'Subscriptions changed',
        data: {
            oldLength: oldSubs?.length || 0,
            newLength: newSubs?.length || 0,
            newSubscriptions: newSubs || []
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'C'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(logData)}).catch(()=>{});
}, { deep: true });

// #endregion

function getServiceName(serviceId) {
    // #region agent log
    const service = serviceStore.services.find(s => s.id === serviceId);
    const result = service?.translations?.[locale.value]?.name ?? `#${serviceId}`;
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:getServiceName',message:'Getting service name',data:{serviceId,hasService:!!service,servicesLength:serviceStore.services?.length||0,result},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
    // #endregion
    return result;
}

function getServiceSubtitle(serviceId) {
    // #region agent log
    const service = serviceStore.services.find(s => s.id === serviceId);
    const result = service?.translations?.[locale.value]?.subtitle ?? null;
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:getServiceSubtitle',message:'Getting service subtitle',data:{serviceId,hasService:!!service,result},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
    // #endregion
    return result;
}

function getServiceLogo(id) {
    // #region agent log
    const service = serviceStore.services.find(s => s.id === id);
    const result = service?.logo ?? '';
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:getServiceLogo',message:'Getting service logo',data:{serviceId:id,hasService:!!service,result},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
    // #endregion
    return result;
}

function getServiceAmount(id) {
    // #region agent log
    const service = serviceStore.services.find(s => s.id === id);
    const result = service?.amount.toFixed(2) ?? '';
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'SubscriptionsPage.vue:getServiceAmount',message:'Getting service amount',data:{serviceId:id,hasService:!!service,result},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'G'})}).catch(()=>{});
    // #endregion
    return result;
}

const filteredSubscriptions = computed(() => {
    // #region agent log
    const beforeFilterData = {
        location: 'SubscriptionsPage.vue:filteredSubscriptions',
        message: 'Computing filtered subscriptions',
        data: {
            hasUser: !!authStore.user,
            hasSubscriptions: !!authStore.user?.subscriptions,
            subscriptionsLength: authStore.user?.subscriptions?.length || 0,
            activeTab: activeTab.value,
            allSubscriptions: authStore.user?.subscriptions || []
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'D'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(beforeFilterData)}).catch(()=>{});
    // #endregion
    
    if (!authStore.user?.subscriptions?.length) return [];

    const filtered = authStore.user.subscriptions.filter(sub => {
        return activeTab.value === 'active' ? sub.status === 'active' : sub.status !== 'active';
    });
    
    // #region agent log
    const afterFilterData = {
        location: 'SubscriptionsPage.vue:filteredSubscriptions',
        message: 'Filtered subscriptions result',
        data: {
            filteredLength: filtered.length,
            filtered: filtered
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'D'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(afterFilterData)}).catch(()=>{});
    // #endregion
    
    return filtered;
});

// #region agent log
watch(() => locale.value, (newLocale, oldLocale) => {
    const logData = {
        location: 'SubscriptionsPage.vue:watch:locale',
        message: 'Locale changed',
        data: {
            oldLocale,
            newLocale,
            hasUser: !!authStore.user,
            hasSubscriptions: !!authStore.user?.subscriptions,
            subscriptionsLength: authStore.user?.subscriptions?.length || 0,
            filteredSubscriptionsLength: filteredSubscriptions.value.length,
            hasServices: !!serviceStore.services,
            servicesLength: serviceStore.services?.length || 0
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'H'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(logData)}).catch(()=>{});
});

watch(() => filteredSubscriptions.value, (newFiltered, oldFiltered) => {
    const logData = {
        location: 'SubscriptionsPage.vue:watch:filteredSubscriptions',
        message: 'Filtered subscriptions changed',
        data: {
            oldLength: oldFiltered?.length || 0,
            newLength: newFiltered?.length || 0,
            newFiltered: newFiltered || [],
            currentLocale: locale.value
        },
        timestamp: Date.now(),
        sessionId: 'debug-session',
        runId: 'run1',
        hypothesisId: 'I'
    };
    fetch('http://127.0.0.1:7243/ingest/2d4847af-9357-42a3-b2e4-f6ffc47c0ee5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(logData)}).catch(()=>{});
}, { deep: true });
// #endregion

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
.service-logo {
    height: 100px;
    padding: 10px;
    width: 124px;
}

.service-item {
    border-bottom: 1px solid gray;
    padding-bottom: 10px;
}
</style>
