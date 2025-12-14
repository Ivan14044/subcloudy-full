import Store from 'electron-store';
import { join } from 'path';
import { app } from 'electron';
import { AuthManager } from './auth';

export interface ActivityRecord {
  id: string;
  sessionId?: string; // ID сессии для связи записей start/stop
  userId: number;
  serviceId: number;
  serviceName: string;
  action: 'session_started' | 'session_stopped';
  timestamp: number;
  duration?: number; // для session_stopped (в секундах)
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

export class ActivityLogger {
  private store: Store;
  private authManager: AuthManager;
  private syncInterval: NodeJS.Timeout | null = null;
  private readonly STORE_KEY = 'activity_history';
  private readonly SYNC_BATCH_SIZE = 50;
  private readonly SYNC_INTERVAL = 10 * 60 * 1000; // 10 минут

  constructor(store: Store, authManager: AuthManager) {
    this.store = store;
    this.authManager = authManager;
    this.startPeriodicSync();
  }

  /**
   * Логирование активности
   */
  logActivity(
    userId: number,
    serviceId: number,
    serviceName: string,
    action: 'session_started' | 'session_stopped',
    duration?: number,
    sessionId?: string
  ): void {
    const history = this.getHistory();

    if (action === 'session_stopped' && sessionId) {
      // Ищем соответствующую запись session_started для обновления
      const startedRecord = history.find(
        r => r.sessionId === sessionId && 
             r.action === 'session_started' && 
             r.userId === userId && 
             r.serviceId === serviceId
      );

      if (startedRecord) {
        // Обновляем существующую запись
        startedRecord.action = 'session_stopped';
        startedRecord.duration = duration;
        startedRecord.synced = false; // Помечаем как несинхронизированную для повторной отправки
        this.store.set(this.STORE_KEY, history);
        
        // Немедленная синхронизация для session_stopped, чтобы данные сразу попадали в админ-панель
        if (this.authManager.isAuthenticated()) {
          this.syncWithBackend().catch(error => {
            console.error('[ActivityLogger] Immediate sync error for session_stopped:', error);
          });
        }
        console.log('[ActivityLogger] Activity updated:', startedRecord.id, action);
        return;
      }
    }

    // Создаем новую запись (для session_started или если не найдена запись для session_stopped)
    const record: ActivityRecord = {
      id: `activity-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      sessionId,
      userId,
      serviceId,
      serviceName,
      action,
      timestamp: Date.now(),
      duration,
      synced: false
    };

    history.push(record);

    // Храним только последние 10000 записей локально
    const maxRecords = 10000;
    if (history.length > maxRecords) {
      history.splice(0, history.length - maxRecords);
    }

    this.store.set(this.STORE_KEY, history);

    // Немедленная синхронизация для session_started и session_stopped, чтобы данные сразу попадали в админ-панель
    if (this.authManager.isAuthenticated()) {
      this.syncWithBackend().catch(error => {
        console.error(`[ActivityLogger] Immediate sync error for ${action}:`, error);
      });
    }

    console.log('[ActivityLogger] Activity logged:', record.id, action);
  }

  /**
   * Получение истории с фильтрами
   */
  getHistory(filters?: ActivityFilters): ActivityRecord[] {
    let history = (this.store.get(this.STORE_KEY, []) as ActivityRecord[]);

    // Применяем фильтры
    if (filters) {
      if (filters.startDate) {
        history = history.filter(record => record.timestamp >= filters.startDate!);
      }
      if (filters.endDate) {
        history = history.filter(record => record.timestamp <= filters.endDate!);
      }
      if (filters.serviceId !== undefined) {
        history = history.filter(record => record.serviceId === filters.serviceId);
      }
      if (filters.action) {
        history = history.filter(record => record.action === filters.action);
      }

      // Сортировка по дате (новые сначала)
      history.sort((a, b) => b.timestamp - a.timestamp);

      // Пагинация
      if (filters.offset !== undefined) {
        history = history.slice(filters.offset);
      }
      if (filters.limit !== undefined) {
        history = history.slice(0, filters.limit);
      }
    } else {
      // По умолчанию сортируем по дате (новые сначала)
      history.sort((a, b) => b.timestamp - a.timestamp);
    }

    return history;
  }

  /**
   * Очистка истории
   */
  clearHistory(): void {
    this.store.delete(this.STORE_KEY);
    console.log('[ActivityLogger] History cleared');
  }

  /**
   * Экспорт истории в CSV
   */
  exportToCSV(filters?: ActivityFilters): string {
    const history = this.getHistory(filters);
    
    const headers = ['ID', 'User ID', 'Service ID', 'Service Name', 'Action', 'Timestamp', 'Duration (seconds)'];
    const rows = history.map(record => [
      record.id,
      record.userId.toString(),
      record.serviceId.toString(),
      record.serviceName,
      record.action,
      new Date(record.timestamp).toISOString(),
      record.duration?.toString() || ''
    ]);

    const csv = [
      headers.join(','),
      ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    return csv;
  }

  /**
   * Экспорт истории в JSON
   */
  exportToJSON(filters?: ActivityFilters): string {
    const history = this.getHistory(filters);
    return JSON.stringify(history, null, 2);
  }

  /**
   * Синхронизация с backend
   */
  async syncWithBackend(): Promise<{ success: boolean; synced: number; error?: string }> {
    try {
      const history = this.getHistory();
      const unsynced = history.filter(record => !record.synced);

      if (unsynced.length === 0) {
        return { success: true, synced: 0 };
      }

      console.log(`[ActivityLogger] Syncing ${unsynced.length} records...`);

      const api = this.authManager.getApiInstance();
      let syncedCount = 0;

      // Отправляем пакетами
      for (let i = 0; i < unsynced.length; i += this.SYNC_BATCH_SIZE) {
        const batch = unsynced.slice(i, i + this.SYNC_BATCH_SIZE);
        
        try {
          // Отправляем каждую запись отдельно (backend ожидает такой формат)
          for (const record of batch) {
            try {
              const response = await api.post('/desktop/log', {
                user_id: record.userId,
                service_id: record.serviceId,
                service_name: record.serviceName,
                action: record.action,
                timestamp: record.timestamp,
                duration: record.duration,
                session_id: record.sessionId
              });
            } catch (syncError: any) {
              throw syncError;
            }
          }

          // Помечаем как синхронизированные
          const allHistory = this.getHistory();
          batch.forEach(batchRecord => {
            const record = allHistory.find(r => r.id === batchRecord.id);
            if (record) {
              record.synced = true;
            }
          });

          this.store.set(this.STORE_KEY, allHistory);
          syncedCount += batch.length;

          console.log(`[ActivityLogger] Synced batch: ${batch.length} records`);
        } catch (error: any) {
          console.error('[ActivityLogger] Batch sync error:', error);
          // Продолжаем с следующим пакетом
        }
      }

      console.log(`[ActivityLogger] Sync completed: ${syncedCount}/${unsynced.length} records synced`);
      return { success: true, synced: syncedCount };
    } catch (error: any) {
      console.error('[ActivityLogger] Sync error:', error);
      return { success: false, synced: 0, error: error.message };
    }
  }

  /**
   * Запуск периодической синхронизации
   */
  private startPeriodicSync() {
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
    }

    this.syncInterval = setInterval(() => {
      if (this.authManager.isAuthenticated()) {
        this.syncWithBackend().catch(error => {
          console.error('[ActivityLogger] Periodic sync error:', error);
        });
      }
    }, this.SYNC_INTERVAL);

    console.log('[ActivityLogger] Periodic sync started');
  }

  /**
   * Остановка периодической синхронизации
   */
  stopPeriodicSync() {
    if (this.syncInterval) {
      clearInterval(this.syncInterval);
      this.syncInterval = null;
    }
  }

  /**
   * Получение статистики синхронизации
   */
  getSyncStats(): { total: number; synced: number; unsynced: number } {
    const history = this.getHistory();
    const synced = history.filter(r => r.synced).length;
    return {
      total: history.length,
      synced,
      unsynced: history.length - synced
    };
  }
}

