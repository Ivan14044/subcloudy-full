<template>
  <div class="history-page min-h-screen bg-[#fafafa] dark:bg-gray-900">
    <!-- Animated gradient background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
      <div class="animated-gradient absolute w-[120vw] h-[120vh] opacity-40 blur-[70px]" />
    </div>

    <div class="relative z-10">
      <!-- Back to services -->
      <div class="max-w-7xl mx-auto px-4 pt-8">
        <button @click="$router.push('/services')" class="back-button">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ $t('services.backToServices') }}
        </button>
      </div>

      <!-- Page Content -->
      <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-light text-gray-900 dark:text-white mb-6">
            {{ $t('history.title') }}
          </h1>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl mb-6">
          <ActivityFilters
            :services="availableServices"
            :filters="activityStore.filters"
            @update-filters="handleFiltersUpdate"
          />
        </div>

        <!-- Sync Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-xl mb-6 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'w-3 h-3 rounded-full',
                activityStore.syncStats.unsynced === 0
                  ? 'bg-green-500'
                  : 'bg-yellow-500 animate-pulse'
              ]"
            ></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ $t('history.syncStatus') }}:
              <span v-if="activityStore.syncStats.unsynced === 0" class="text-green-600 dark:text-green-400">
                {{ $t('history.synced') }}
              </span>
              <span v-else class="text-yellow-600 dark:text-yellow-400">
                {{ activityStore.syncStats.unsynced }} {{ $t('history.notSynced') }}
              </span>
            </span>
          </div>
          <button
            @click="handleSync"
            :disabled="activityStore.loading"
            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
          >
            {{ $t('history.syncing') }}
          </button>
        </div>

        <!-- Loading -->
        <div v-if="activityStore.loading && activityStore.history.length === 0" class="loading-container">
          <div class="spinner-large"></div>
          <p class="text-gray-600 dark:text-gray-300 mt-4">{{ $t('history.loading') }}</p>
        </div>

        <!-- History Table -->
        <div v-else-if="activityStore.history.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
          <ActivityTable
            :history="activityStore.history"
            :has-more="activityStore.history.length >= (activityStore.filters.limit || 50)"
            @load-more="handleLoadMore"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <div class="empty-icon">📋</div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
            {{ $t('history.empty') }}
          </h2>
          <p class="text-gray-600 dark:text-gray-300">
            {{ $t('history.emptyDesc') }}
          </p>
        </div>

        <!-- Actions -->
        <div v-if="activityStore.history.length > 0" class="mt-6 flex gap-4 justify-center">
          <button
            @click="handleExport('csv')"
            class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors"
          >
            {{ $t('history.exportCSV') }}
          </button>
          <button
            @click="handleExport('json')"
            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors"
          >
            {{ $t('history.exportJSON') }}
          </button>
          <button
            @click="handleClear"
            class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors"
          >
            {{ $t('history.clear') }}
          </button>
        </div>

        <!-- Error -->
        <div v-if="activityStore.error" class="error-banner">
          {{ activityStore.error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useActivityStore } from '../stores/activity';
import { useServicesStore } from '../stores/services';
import ActivityFilters from '../components/activity/ActivityFilters.vue';
import ActivityTable from '../components/activity/ActivityTable.vue';

const router = useRouter();
const activityStore = useActivityStore();
const servicesStore = useServicesStore();
const { t } = useI18n();

const availableServices = computed(() => servicesStore.services);

async function handleFiltersUpdate(filters: any) {
  await activityStore.applyFilters(filters);
}

async function handleLoadMore() {
  const currentOffset = activityStore.filters.offset || 0;
  const limit = activityStore.filters.limit || 50;
  await activityStore.fetchHistory({
    ...activityStore.filters,
    offset: currentOffset + limit
  }, true); // append = true для добавления к существующей истории
}

async function handleExport(format: 'csv' | 'json') {
  await activityStore.exportHistory(format);
}

async function handleClear() {
  if (confirm(t('history.clearConfirm'))) {
    await activityStore.clearHistory();
  }
}

async function handleSync() {
  await activityStore.sync();
  await activityStore.updateSyncStats();
}

onMounted(async () => {
  // Загружаем сервисы для фильтров
  if (servicesStore.services.length === 0) {
    await servicesStore.fetchServices();
  }

  // Загружаем историю
  await activityStore.fetchHistory();
  await activityStore.updateSyncStats();
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

.dark .back-button:hover {
  color: #60a5fa;
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

.error-banner {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 16px 24px;
  background: rgba(239, 68, 68, 0.95);
  color: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
  max-width: 500px;
  text-align: center;
  animation: slideUp 0.3s ease-out;
  z-index: 1000;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translate(-50%, 20px);
  }
  to {
    opacity: 1;
    transform: translate(-50%, 0);
  }
}
</style>

