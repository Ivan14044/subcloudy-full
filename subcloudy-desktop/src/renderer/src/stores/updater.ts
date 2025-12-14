import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface UpdateStatus {
  status: 'checking' | 'available' | 'downloading' | 'ready' | 'error' | 'idle';
  version?: string;
  progress?: number;
  error?: string;
  releaseNotes?: string;
  size?: number;
}

export interface UpdateSettings {
  autoCheckUpdates: boolean;
  autoDownloadUpdates: boolean;
  updateCheckInterval: number;
}

export const useUpdaterStore = defineStore('updater', () => {
  const status = ref<UpdateStatus>({ status: 'idle' });
  const settings = ref<UpdateSettings>({
    autoCheckUpdates: true,
    autoDownloadUpdates: false,
    updateCheckInterval: 4 * 60 * 60 * 1000 // 4 часа
  });
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Инициализация: загрузка настроек и подписка на изменения статуса
  async function init() {
    try {
      // Загружаем настройки
      const loadedSettings = await window.electronAPI.updater.getSettings();
      settings.value = loadedSettings;

      // Подписываемся на изменения статуса
      window.electronAPI.updater.onStatusChange((newStatus: UpdateStatus) => {
        status.value = newStatus;
      });

      // Получаем текущий статус
      status.value = await window.electronAPI.updater.getStatus();
    } catch (err: any) {
      console.error('[UpdaterStore] Init error:', err);
      error.value = err.message;
    }
  }

  // Проверка обновлений
  async function checkForUpdates(force = false) {
    loading.value = true;
    error.value = null;

    try {
      const result = await window.electronAPI.updater.checkForUpdates(force);
      status.value = result;
      return result;
    } catch (err: any) {
      error.value = err.message;
      status.value = { status: 'error', error: err.message };
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Скачивание обновления
  async function downloadUpdate() {
    loading.value = true;
    error.value = null;

    try {
      const result = await window.electronAPI.updater.downloadUpdate();
      status.value = result;
      return result;
    } catch (err: any) {
      error.value = err.message;
      status.value = { status: 'error', error: err.message };
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Установка обновления
  async function installUpdate() {
    loading.value = true;
    error.value = null;

    try {
      await window.electronAPI.updater.installUpdate();
    } catch (err: any) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Обновление настроек
  async function updateSettings(newSettings: Partial<UpdateSettings>) {
    try {
      await window.electronAPI.updater.updateSettings(newSettings);
      settings.value = { ...settings.value, ...newSettings };
      return { success: true };
    } catch (err: any) {
      error.value = err.message;
      return { success: false, error: err.message };
    }
  }

  return {
    status,
    settings,
    loading,
    error,
    init,
    checkForUpdates,
    downloadUpdate,
    installUpdate,
    updateSettings
  };
});


