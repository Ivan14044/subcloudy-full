import { autoUpdater } from 'electron-updater';
import { app } from 'electron';
import Store from 'electron-store';

export interface UpdateStatus {
  status: 'checking' | 'available' | 'downloading' | 'ready' | 'error' | 'idle';
  version?: string;
  progress?: number;
  error?: string;
  releaseNotes?: string;
  size?: number;
}

export class UpdateManager {
  private store: Store;
  private checkInterval: NodeJS.Timeout | null = null;
  private statusListeners: Set<(status: UpdateStatus) => void> = new Set();
  private currentStatus: UpdateStatus = { status: 'idle' };

  constructor(store: Store) {
    this.store = store;
    this.configureUpdater();
  }

  /**
   * Настройка electron-updater
   */
  private configureUpdater() {
    // Настройка автоматической проверки обновлений
    autoUpdater.autoDownload = this.store.get('autoDownloadUpdates', false) as boolean;
    autoUpdater.autoInstallOnAppQuit = true;

    // Настройка канала обновлений (stable/beta)
    autoUpdater.channel = 'latest';

    // Обработка событий обновлений
    autoUpdater.on('checking-for-update', () => {
      console.log('[Updater] Checking for updates...');
      this.updateStatus({ status: 'checking' });
    });

    autoUpdater.on('update-available', (info) => {
      console.log('[Updater] Update available:', info.version);
      this.updateStatus({
        status: 'available',
        version: info.version,
        releaseNotes: typeof info.releaseNotes === 'string' ? info.releaseNotes : (Array.isArray(info.releaseNotes) ? info.releaseNotes.map(n => typeof n === 'string' ? n : n?.note || '').join('\n') : undefined),
        size: info.files?.[0]?.size
      });
    });

    autoUpdater.on('update-not-available', () => {
      console.log('[Updater] No updates available');
      this.updateStatus({ status: 'idle' });
    });

    autoUpdater.on('update-downloaded', (info) => {
      console.log('[Updater] Update downloaded:', info.version);
      this.updateStatus({
        status: 'ready',
        version: info.version
      });
    });

    autoUpdater.on('download-progress', (progress) => {
      console.log('[Updater] Download progress:', Math.round(progress.percent), '%');
      this.updateStatus({
        status: 'downloading',
        progress: Math.round(progress.percent)
      });
    });

    autoUpdater.on('error', (error) => {
      console.error('[Updater] Error:', error);
      // Дополнительное логирование для отладки на стороне пользователя
      if (error.message.includes('electron-updater')) {
        console.error('[Updater] Critical: electron-updater module error. Check dependencies.');
      }
      this.updateStatus({
        status: 'error',
        error: `Ошибка обновления: ${error.message}`
      });
    });
  }

  /**
   * Обновление статуса и уведомление слушателей
   */
  private updateStatus(status: UpdateStatus) {
    this.currentStatus = { ...this.currentStatus, ...status };
    this.statusListeners.forEach(listener => listener(this.currentStatus));
  }

  /**
   * Проверка обновлений
   */
  async checkForUpdates(force = false): Promise<UpdateStatus> {
    try {
      if (!force && !this.store.get('autoCheckUpdates', true)) {
        console.log('[Updater] Auto-check disabled');
        return { status: 'idle' };
      }

      console.log('[Updater] Checking for updates...');
      await autoUpdater.checkForUpdates();
      return this.currentStatus;
    } catch (error: any) {
      console.error('[Updater] Check failed:', error);
      this.updateStatus({
        status: 'error',
        error: error.message
      });
      return this.currentStatus;
    }
  }

  /**
   * Скачивание обновления
   */
  async downloadUpdate(): Promise<UpdateStatus> {
    try {
      console.log('[Updater] Downloading update...');
      await autoUpdater.downloadUpdate();
      return this.currentStatus;
    } catch (error: any) {
      console.error('[Updater] Download failed:', error);
      this.updateStatus({
        status: 'error',
        error: error.message
      });
      return this.currentStatus;
    }
  }

  /**
   * Установка обновления и перезапуск
   */
  async installUpdate(): Promise<void> {
    try {
      console.log('[Updater] Installing update and restarting...');
      autoUpdater.quitAndInstall(false, true);
    } catch (error: any) {
      console.error('[Updater] Install failed:', error);
      throw error;
    }
  }

  /**
   * Получение информации об обновлении
   */
  getUpdateInfo(): UpdateStatus {
    return { ...this.currentStatus };
  }

  /**
   * Запуск периодической проверки обновлений
   */
  startPeriodicCheck() {
    const interval = this.store.get('updateCheckInterval', 4 * 60 * 60 * 1000) as number; // 4 часа по умолчанию
    
    if (this.checkInterval) {
      clearInterval(this.checkInterval);
    }

    this.checkInterval = setInterval(() => {
      this.checkForUpdates();
    }, interval);

    console.log(`[Updater] Periodic check started (interval: ${interval / 1000 / 60} minutes)`);
  }

  /**
   * Остановка периодической проверки
   */
  stopPeriodicCheck() {
    if (this.checkInterval) {
      clearInterval(this.checkInterval);
      this.checkInterval = null;
    }
  }

  /**
   * Подписка на изменения статуса
   */
  onStatusChange(listener: (status: UpdateStatus) => void) {
    this.statusListeners.add(listener);
    // Сразу отправляем текущий статус
    listener(this.currentStatus);
    
    return () => {
      this.statusListeners.delete(listener);
    };
  }

  /**
   * Получение настроек обновлений
   */
  getSettings() {
    return {
      autoCheckUpdates: this.store.get('autoCheckUpdates', true),
      autoDownloadUpdates: this.store.get('autoDownloadUpdates', false),
      updateCheckInterval: this.store.get('updateCheckInterval', 4 * 60 * 60 * 1000)
    };
  }

  /**
   * Обновление настроек
   */
  updateSettings(settings: Partial<{
    autoCheckUpdates: boolean;
    autoDownloadUpdates: boolean;
    updateCheckInterval: number;
  }>) {
    if (settings.autoCheckUpdates !== undefined) {
      this.store.set('autoCheckUpdates', settings.autoCheckUpdates);
    }
    if (settings.autoDownloadUpdates !== undefined) {
      this.store.set('autoDownloadUpdates', settings.autoDownloadUpdates);
      autoUpdater.autoDownload = settings.autoDownloadUpdates;
    }
    if (settings.updateCheckInterval !== undefined) {
      this.store.set('updateCheckInterval', settings.updateCheckInterval);
      // Перезапускаем периодическую проверку с новым интервалом
      this.startPeriodicCheck();
    }
  }
}

