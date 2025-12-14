<template>
  <div class="subscriptions-page min-h-screen bg-[#fafafa] dark:bg-gray-900">
    <!-- Animated gradient background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="animated-gradient absolute w-[120vw] h-[120vh] opacity-40 blur-[70px]" />
    </div>

    <div class="relative z-10">
      <!-- Back to services -->
      <div class="max-w-4xl mx-auto px-4 pt-8">
        <button @click="$router.push('/services')" class="back-button">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ $t('services.backToServices') }}
        </button>
      </div>

      <!-- Page Content -->
      <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-light text-gray-900 dark:text-white mb-6">
            {{ $t('subscriptions.title') }}
          </h1>

          <!-- Tabs -->
          <div class="flex justify-center gap-4">
            <button
              :class="[
                'px-6 py-2 rounded-lg font-medium transition-all',
                activeTab === 'active'
                  ? 'bg-blue-500 dark:bg-blue-900 text-white shadow-lg'
                  : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-600'
              ]"
              @click="activeTab = 'active'"
            >
              {{ $t('subscriptions.active_tab') }}
            </button>
            <button
              :class="[
                'px-6 py-2 rounded-lg font-medium transition-all',
                activeTab === 'inactive'
                  ? 'bg-blue-500 dark:bg-blue-900 text-white shadow-lg'
                  : 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-600'
              ]"
              @click="activeTab = 'inactive'"
            >
              {{ $t('subscriptions.inactive_tab') }}
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-container">
          <div class="spinner-large"></div>
          <p class="text-gray-600 dark:text-gray-300 mt-4">{{ $t('subscriptions.loading') }}</p>
        </div>

        <!-- Subscriptions List -->
        <div v-else-if="filteredSubscriptions.length" class="space-y-6 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl">
          <div
            v-for="subscription in filteredSubscriptions"
            :key="subscription.id"
            class="d-flex align-center gap-3 flex-wrap service-item"
          >
            <div class="flex-1 min-w-[200px]">
              <!-- Service Header -->
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-1">
                    {{ getServiceName(subscription.service_id) }}
                  </h3>
                  <p class="text-sm text-gray-900 dark:text-gray-400 leading-relaxed">
                    <strong>${{ getServiceAmount(subscription.service_id) }}</strong> / {{ $t('subscriptions.month') }}
                  </p>

                  <!-- Subscription Details -->
                  <p class="text-[12px] dark:!text-gray-400">
                    {{
                      $t('subscriptions.payment_method_label')
                    }}: {{ $t('checkout.' + subscription.payment_method) }}
                  </p>
                  <p class="text-[12px] dark:!text-gray-400">
                    {{ $t('subscriptions.created_label') }}: {{ formatDate(subscription.created_at) }}
                  </p>
                  <template v-if="subscription.payment_method === 'credit_card' && subscription.status === 'active'">
                    <p v-if="subscription.is_auto_renew" class="text-[12px] dark:!text-gray-400">
                      {{ $t('subscriptions.next_payment_label') }}: {{ formatDate(subscription.next_payment_at) }}
                    </p>
                    <p v-else class="text-[12px] dark:!text-gray-400">
                      {{ $t('subscriptions.expires_on') }}: {{ formatDate(subscription.next_payment_at) }}
                    </p>
                  </template>

                  <!-- Time indicator with progress bar -->
                  <div class="mt-3 space-y-2" v-if="subscription.status === 'active'">
                    <div class="flex items-center justify-between text-xs">
                      <span :class="getTextColor(subscription.days_left)" class="font-semibold">
                        ⏰ {{ subscription.days_left }} {{ $t('subscriptions.days_left') }}
                      </span>
                      <span class="text-gray-500 dark:text-gray-400">
                        {{ Math.round(subscription.progress_percent) }}%
                      </span>
                    </div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                      <div
                        class="h-full transition-all duration-500"
                        :class="getProgressColor(subscription.days_left)"
                        :style="{ width: subscription.progress_percent + '%' }"
                      ></div>
                    </div>
                    <div v-if="subscription.days_left <= 7" class="text-xs font-medium" :class="getTextColor(subscription.days_left)">
                      ⚠️ {{ getWarningText(subscription.days_left) }}
                    </div>
                  </div>
                </div>
                <img
                  :src="getServiceLogo(subscription.service_id)"
                  :alt="getServiceName(subscription.service_id)"
                  class="w-[100px] h-[100px] object-contain object-center service-logo"
                />
              </div>

              <!-- Actions -->
              <div v-if="subscription.status === 'active'" class="flex gap-2 mt-2 items-stretch h-8">
                <div :class="subscription.status !== 'active' || subscription.payment_method !== 'credit_card' ? 'w-full' : 'w-1/2'">
                  <span class="inline-block w-full h-full text-xs font-medium px-2 py-1 rounded-lg text-center flex items-center justify-center bg-green-400 text-gray-800">
                    {{ $t('subscriptions.status_' + subscription.status) }}
                  </span>
                </div>

                <div v-if="subscription.status === 'active' && subscription.payment_method === 'credit_card'" class="w-1/2">
                  <button
                    type="button"
                    class="w-full h-full bg-black text-white rounded-lg hover:bg-gray-800 text-sm transition-colors"
                    @click="toggleAutoRenew(subscription.id, subscription.is_auto_renew)"
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

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">📦</div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ activeTab === 'active' ? $t('subscriptions.empty_active') : $t('subscriptions.empty_inactive') }}
          </h2>
          <button @click="$router.push('/services')" class="btn-primary mt-4">
            {{ $t('services.backToServices') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useServicesStore } from '../stores/services';
import { useI18n } from 'vue-i18n';

const authStore = useAuthStore();
const servicesStore = useServicesStore();
const { t, locale } = useI18n();

const activeTab = ref<'active' | 'inactive'>('active');
const loading = ref(false);

const filteredSubscriptions = computed(() => {
  if (!authStore.user?.subscriptions) return [];

  return authStore.user.subscriptions
    .filter(sub => (activeTab.value === 'active' ? sub.status === 'active' : sub.status !== 'active'))
    .map(sub => {
      const daysLeft = calculateDaysLeft(sub.next_payment_at);
      const progressPercent = calculateProgress(sub.created_at, sub.next_payment_at);

      return {
        ...sub,
        days_left: daysLeft,
        progress_percent: progressPercent
      };
    });
});

function calculateDaysLeft(nextPaymentAt: string): number {
  const now = new Date();
  const expiry = new Date(nextPaymentAt);
  const diff = expiry.getTime() - now.getTime();
  return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

function calculateProgress(createdAt: string, nextPaymentAt: string): number {
  const created = new Date(createdAt);
  const expiry = new Date(nextPaymentAt);
  const now = new Date();

  const total = expiry.getTime() - created.getTime();
  const passed = now.getTime() - created.getTime();

  return Math.min(100, Math.max(0, (passed / total) * 100));
}

function getServiceName(serviceId: number): string {
  const service = servicesStore.services.find((s: any) => s.id === serviceId);
  return service?.translations?.[locale.value]?.name || service?.name || `Service #${serviceId}`;
}

function getServiceAmount(serviceId: number): string {
  const service = servicesStore.services.find((s: any) => s.id === serviceId);
  return service?.amount?.toFixed(2) || '0.00';
}

function getServiceLogo(serviceId: number): string {
  const service = servicesStore.services.find((s: any) => s.id === serviceId);
  return service?.logo || '';
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString();
}

function getProgressColor(daysLeft: number): string {
  if (daysLeft <= 3) return 'bg-red-500';
  if (daysLeft <= 7) return 'bg-orange-500';
  if (daysLeft <= 14) return 'bg-yellow-500';
  return 'bg-green-500';
}

function getTextColor(daysLeft: number): string {
  if (daysLeft <= 3) return 'text-red-600 dark:text-red-400';
  if (daysLeft <= 7) return 'text-orange-600 dark:text-orange-400';
  return 'text-green-600 dark:text-green-400';
}

function getWarningClass(daysLeft: number): string {
  if (daysLeft <= 3) return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200';
  return 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-200';
}

function getWarningText(daysLeft: number): string {
  if (daysLeft <= 1) return t('subscriptions.expires_tomorrow');
  if (daysLeft <= 3) return t('subscriptions.expires_very_soon');
  return t('subscriptions.expires_soon');
}

function getStatusBadgeClass(status: string): string {
  if (status === 'active') return 'bg-green-400 text-gray-800';
  if (status === 'canceled') return 'bg-red-400 text-white';
  return 'bg-gray-400 text-white';
}

async function toggleAutoRenew(id: number, isAutoRenew: boolean) {
  const confirmed = confirm(
    isAutoRenew ? t('subscriptions.cancel_confirm') : t('subscriptions.renew_confirm')
  );

  if (!confirmed) return;

  try {
    loading.value = true;
    
    // Вызов через electronAPI
    const result = await window.electronAPI.auth.toggleAutoRenew(id);
    
    if (result.success) {
      alert(t('subscriptions.toggle_success'));
      // Обновить данные
      await authStore.checkAuth();
    } else {
      alert(result.error || t('subscriptions.toggle_error'));
    }
  } catch (error) {
    console.error('[SubscriptionsPage] Toggle error:', error);
    alert(t('subscriptions.toggle_error'));
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  loading.value = true;
  
  // Загружаем данные сервисов и пользователя параллельно
  await Promise.all([
    servicesStore.fetchServices(),
    authStore.checkAuth()
  ]);
  
  loading.value = false;
});
</script>

<style scoped>
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
  0%, 100% { transform: translate(-18%, -18%) rotate(0deg) scale(1); }
  25% { transform: translate(-10%, -22%) rotate(20deg) scale(1.03); }
  50% { transform: translate(8%, -12%) rotate(40deg) scale(0.98); }
  75% { transform: translate(-12%, 8%) rotate(25deg) scale(1.02); }
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  color: #374151;
  transition: color 0.2s;
}

.dark .back-button {
  color: #d1d5db;
}

.back-button:hover {
  color: #2563eb;
}

.service-item {
  border-bottom: 1px solid gray;
  padding-bottom: 10px;
  margin-bottom: 10px;
}

.service-logo {
  height: 100px;
  padding: 10px;
  width: 124px;
}

.d-flex {
  display: flex;
}

.align-center {
  align-items: center;
}

.gap-3 {
  gap: 0.75rem;
}

.flex-wrap {
  flex-wrap: wrap;
}

.flex-1 {
  flex: 1;
}

.min-w-\[200px\] {
  min-width: 200px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
  padding: 40px 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  font-weight: 500;
  border-radius: 0.5rem;
  transition: all 0.2s;
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
  transform: translateY(-1px);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(59, 130, 246, 0.2);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>

