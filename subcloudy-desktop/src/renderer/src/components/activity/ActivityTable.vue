<template>
  <div class="activity-table">
    <table class="w-full">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $t('history.date') }}
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $t('history.service') }}
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $t('history.action') }}
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $t('history.duration') }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        <tr
          v-for="record in history"
          :key="record.id"
          class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ formatDate(record.timestamp) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            {{ record.serviceName }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                record.action === 'session_started'
                  ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
              ]"
            >
              {{ record.action === 'session_started' ? $t('history.sessionStarted') : $t('history.sessionStopped') }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
            <span v-if="record.duration">
              {{ formatDuration(record.duration) }}
            </span>
            <span v-else class="text-gray-400">—</span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Load More Button -->
    <div v-if="hasMore" class="p-4 text-center border-t border-gray-200 dark:border-gray-700">
      <button
        @click="$emit('load-more')"
        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors"
      >
        {{ $t('history.loadMore') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
  history: Array<{
    id: string;
    timestamp: number;
    serviceName: string;
    action: 'session_started' | 'session_stopped';
    duration?: number;
  }>;
  hasMore?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  hasMore: false
});

defineEmits(['load-more']);

const { t } = useI18n();

function formatDate(timestamp: number): string {
  const date = new Date(timestamp);
  return date.toLocaleString();
}

function formatDuration(seconds: number): string {
  if (seconds < 60) {
    return `${seconds} ${t('history.seconds')}`;
  }
  if (seconds < 3600) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes} ${t('history.minutes')} ${secs > 0 ? `${secs} ${t('history.seconds')}` : ''}`;
  }
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  return `${hours} ${t('history.hours')} ${minutes > 0 ? `${minutes} ${t('history.minutes')}` : ''}`;
}
</script>

<style scoped>
/* Дополнительные стили если нужно */
</style>


