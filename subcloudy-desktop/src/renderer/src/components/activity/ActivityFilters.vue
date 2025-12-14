<template>
  <div class="activity-filters space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Date Range Filter -->
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('history.filters.dateRange') }}
        </label>
        <select
          v-model="localFilters.datePreset"
          @change="handleDatePresetChange"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="today">{{ $t('history.filters.today') }}</option>
          <option value="week">{{ $t('history.filters.week') }}</option>
          <option value="month">{{ $t('history.filters.month') }}</option>
          <option value="allTime">{{ $t('history.filters.allTime') }}</option>
          <option value="custom">{{ $t('history.filters.custom') }}</option>
        </select>
      </div>

      <!-- Service Filter -->
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('history.filters.service') }}
        </label>
        <select
          v-model="localFilters.serviceId"
          @change="handleFilterChange"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option :value="undefined">{{ $t('history.filters.allServices') }}</option>
          <option
            v-for="service in services"
            :key="service.id"
            :value="service.id"
          >
            {{ service.name }}
          </option>
        </select>
      </div>

      <!-- Action Filter -->
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('history.filters.action') }}
        </label>
        <select
          v-model="localFilters.action"
          @change="handleFilterChange"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option :value="undefined">{{ $t('history.filters.allActions') }}</option>
          <option value="session_started">{{ $t('history.sessionStarted') }}</option>
          <option value="session_stopped">{{ $t('history.sessionStopped') }}</option>
        </select>
      </div>
    </div>

    <!-- Custom Date Range (shown when custom is selected) -->
    <div v-if="localFilters.datePreset === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('history.filters.startDate') }}
        </label>
        <input
          v-model="localFilters.startDateInput"
          type="date"
          @change="handleCustomDateChange"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ $t('history.filters.endDate') }}
        </label>
        <input
          v-model="localFilters.endDateInput"
          type="date"
          @change="handleCustomDateChange"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
  services: Array<{ id: number; name: string }>;
  filters: {
    startDate?: number;
    endDate?: number;
    serviceId?: number;
    action?: 'session_started' | 'session_stopped';
  };
}

const props = defineProps<Props>();
const emit = defineEmits(['update-filters']);

const { t } = useI18n();

const localFilters = ref({
  datePreset: 'allTime' as 'today' | 'week' | 'month' | 'allTime' | 'custom',
  serviceId: undefined as number | undefined,
  action: undefined as 'session_started' | 'session_stopped' | undefined,
  startDateInput: '',
  endDateInput: ''
});

function getDatePreset() {
  if (!props.filters.startDate && !props.filters.endDate) {
    return 'allTime';
  }

  const now = Date.now();
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const weekAgo = new Date(today);
  weekAgo.setDate(weekAgo.getDate() - 7);
  const monthAgo = new Date(today);
  monthAgo.setMonth(monthAgo.getMonth() - 1);

  if (props.filters.startDate && props.filters.startDate >= today.getTime()) {
    return 'today';
  }
  if (props.filters.startDate && props.filters.startDate >= weekAgo.getTime()) {
    return 'week';
  }
  if (props.filters.startDate && props.filters.startDate >= monthAgo.getTime()) {
    return 'month';
  }

  return 'custom';
}

function handleDatePresetChange() {
  const now = Date.now();
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const weekAgo = new Date(today);
  weekAgo.setDate(weekAgo.getDate() - 7);
  const monthAgo = new Date(today);
  monthAgo.setMonth(monthAgo.getMonth() - 1);

  let startDate: number | undefined;
  let endDate: number | undefined = now;

  switch (localFilters.value.datePreset) {
    case 'today':
      startDate = today.getTime();
      break;
    case 'week':
      startDate = weekAgo.getTime();
      break;
    case 'month':
      startDate = monthAgo.getTime();
      break;
    case 'allTime':
      startDate = undefined;
      endDate = undefined;
      break;
    case 'custom':
      // Оставляем текущие значения или устанавливаем по умолчанию
      if (localFilters.value.startDateInput) {
        startDate = new Date(localFilters.value.startDateInput).getTime();
      }
      if (localFilters.value.endDateInput) {
        endDate = new Date(localFilters.value.endDateInput).getTime() + 24 * 60 * 60 * 1000 - 1;
      }
      break;
  }

  emit('update-filters', {
    startDate,
    endDate,
    serviceId: localFilters.value.serviceId,
    action: localFilters.value.action
  });
}

function handleCustomDateChange() {
  if (localFilters.value.datePreset === 'custom') {
    handleDatePresetChange();
  }
}

function handleFilterChange() {
  emit('update-filters', {
    startDate: props.filters.startDate,
    endDate: props.filters.endDate,
    serviceId: localFilters.value.serviceId,
    action: localFilters.value.action
  });
}

onMounted(() => {
  localFilters.value.datePreset = getDatePreset();
  localFilters.value.serviceId = props.filters.serviceId;
  localFilters.value.action = props.filters.action;

  if (props.filters.startDate) {
    localFilters.value.startDateInput = new Date(props.filters.startDate).toISOString().split('T')[0];
  }
  if (props.filters.endDate) {
    localFilters.value.endDateInput = new Date(props.filters.endDate).toISOString().split('T')[0];
  }
});

watch(() => props.filters, () => {
  localFilters.value.datePreset = getDatePreset();
  localFilters.value.serviceId = props.filters.serviceId;
  localFilters.value.action = props.filters.action;

  if (props.filters.startDate) {
    localFilters.value.startDateInput = new Date(props.filters.startDate).toISOString().split('T')[0];
  }
  if (props.filters.endDate) {
    localFilters.value.endDateInput = new Date(props.filters.endDate).toISOString().split('T')[0];
  }
}, { deep: true });
</script>

<style scoped>
/* Дополнительные стили если нужно */
</style>


