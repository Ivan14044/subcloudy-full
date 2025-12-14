<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <div
      v-if="showNotification"
      class="fixed bottom-4 right-4 max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
    >
      <div class="p-4">
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-2">
            <svg
              class="w-5 h-5 text-blue-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
              />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ $t('updater.updateAvailable') }}
            </h3>
          </div>
          <button
            @click="dismiss"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="mb-4">
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
            {{ $t('updater.newVersionAvailable', { version: updaterStore.status.version }) }}
          </p>

          <!-- Release Notes -->
          <div
            v-if="updaterStore.status.releaseNotes"
            class="text-xs text-gray-500 dark:text-gray-400 mb-3 max-h-32 overflow-y-auto"
            v-html="updaterStore.status.releaseNotes"
          />

          <!-- Download Progress -->
          <div v-if="updaterStore.status.status === 'downloading'" class="mb-3">
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
              <span>{{ $t('updater.downloading') }}</span>
              <span>{{ updaterStore.status.progress || 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div
                class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${updaterStore.status.progress || 0}%` }"
              ></div>
            </div>
          </div>

          <!-- Size Info -->
          <p v-if="updaterStore.status.size" class="text-xs text-gray-500 dark:text-gray-400">
            {{ $t('updater.size') }}: {{ formatSize(updaterStore.status.size) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button
            v-if="updaterStore.status.status === 'available'"
            @click="handleDownload"
            :disabled="updaterStore.loading"
            class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('updater.download') }}
          </button>

          <button
            v-if="updaterStore.status.status === 'ready'"
            @click="handleInstall"
            :disabled="updaterStore.loading"
            class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ $t('updater.installNow') }}
          </button>

          <button
            v-if="updaterStore.status.status === 'downloading'"
            @click="dismiss"
            class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition-colors"
          >
            {{ $t('updater.downloadInBackground') }}
          </button>

          <button
            v-if="updaterStore.status.status === 'available'"
            @click="dismiss"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition-colors"
          >
            {{ $t('updater.later') }}
          </button>
        </div>

        <!-- Error Message -->
        <div
          v-if="updaterStore.status.status === 'error' && updaterStore.status.error"
          class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-xs text-red-600 dark:text-red-400"
        >
          {{ updaterStore.status.error }}
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';
import { useUpdaterStore } from '../stores/updater';
import { useI18n } from 'vue-i18n';

const updaterStore = useUpdaterStore();
const { t } = useI18n();

const showNotification = computed(() => {
  const status = updaterStore.status.status;
  return status === 'available' || status === 'downloading' || status === 'ready' || status === 'error';
});

const dismissed = computed(() => {
  // Можно добавить логику для отслеживания отклоненных уведомлений
  return false;
});

function formatSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

async function handleDownload() {
  try {
    await updaterStore.downloadUpdate();
  } catch (error) {
    console.error('[UpdateNotification] Download error:', error);
  }
}

async function handleInstall() {
  try {
    await updaterStore.installUpdate();
  } catch (error) {
    console.error('[UpdateNotification] Install error:', error);
  }
}

function dismiss() {
  // Можно добавить логику для скрытия уведомления на некоторое время
  // Пока просто скрываем, но статус остается
}

onMounted(() => {
  updaterStore.init();
});

onUnmounted(() => {
  // Cleanup если нужно
});
</script>

<style scoped>
/* Дополнительные стили если нужно */
</style>


