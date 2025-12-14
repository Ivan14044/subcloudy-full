import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface ActivityRecord {
  id: string;
  userId: number;
  serviceId: number;
  serviceName: string;
  action: 'session_started' | 'session_stopped';
  timestamp: number;
  duration?: number;
  synced: boolean;
}

export interface ActivityFilters {
  startDate?: number;
  endDate?: number;
  serviceId?: number;
  action?: 'session_started' | 'session_stopped';
  limit?: number;
  offset?: number;
}

export const useActivityStore = defineStore('activity', () => {
  const history = ref<ActivityRecord[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const filters = ref<ActivityFilters>({
    limit: 50,
    offset: 0
  });
  const syncStats = ref({ total: 0, synced: 0, unsynced: 0 });

  // Загрузка истории
  async function fetchHistory(newFilters?: ActivityFilters, append = false) {
    loading.value = true;
    error.value = null;

    try {
      if (newFilters) {
        filters.value = { ...filters.value, ...newFilters };
      }

      const data = await window.electronAPI.activity.getHistory(filters.value);
      
      if (append) {
        history.value = [...history.value, ...data];
      } else {
        history.value = data;
      }
      
      return data;
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch history';
      console.error('[ActivityStore] Fetch error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Применение фильтров
  async function applyFilters(newFilters: Partial<ActivityFilters>) {
    filters.value = { ...filters.value, ...newFilters, offset: 0 };
    await fetchHistory();
  }

  // Экспорт истории
  async function exportHistory(format: 'csv' | 'json', exportFilters?: ActivityFilters) {
    try {
      const data = await window.electronAPI.activity.exportHistory(format, exportFilters || filters.value);
      
      // Создаем blob и скачиваем файл
      const blob = new Blob([data], { 
        type: format === 'csv' ? 'text/csv' : 'application/json' 
      });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `subcloudy-history-${new Date().toISOString().split('T')[0]}.${format}`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      return { success: true };
    } catch (err: any) {
      error.value = err.message || 'Export failed';
      return { success: false, error: error.value };
    }
  }

  // Очистка истории
  async function clearHistory() {
    loading.value = true;
    error.value = null;

    try {
      await window.electronAPI.activity.clearHistory();
      history.value = [];
      syncStats.value = { total: 0, synced: 0, unsynced: 0 };
      return { success: true };
    } catch (err: any) {
      error.value = err.message || 'Failed to clear history';
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  }

  // Синхронизация с backend
  async function sync() {
    loading.value = true;
    error.value = null;

    try {
      const result = await window.electronAPI.activity.sync();
      await updateSyncStats();
      return result;
    } catch (err: any) {
      error.value = err.message || 'Sync failed';
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  }

  // Обновление статистики синхронизации
  async function updateSyncStats() {
    try {
      syncStats.value = await window.electronAPI.activity.getSyncStats();
    } catch (err) {
      console.error('[ActivityStore] Failed to get sync stats:', err);
    }
  }

  // Отфильтрованная история (computed)
  const filteredHistory = computed(() => {
    return history.value;
  });

  return {
    history: filteredHistory,
    loading,
    error,
    filters,
    syncStats,
    fetchHistory,
    applyFilters,
    exportHistory,
    clearHistory,
    sync,
    updateSyncStats
  };
});

