import { app, BrowserWindow, ipcMain, session, dialog } from 'electron';
import { join } from 'path';
import { AuthManager } from './auth';
import { ServiceManager } from './services';
import { SecurityManager } from './security';
import { UpdateManager } from './updater';
import { ActivityLogger } from './activityLogger';
import { createMainWindow } from './windows/mainWindow';
import Store from 'electron-store';

// Инициализация защищенного хранилища
const store = new Store({
  encryptionKey: 'subcloudy-secure-key-2024', // В продакшене использовать более безопасный ключ
  name: 'subcloudy-config'
});

let mainWindow: BrowserWindow | null = null;
let authManager: AuthManager;
let serviceManager: ServiceManager;
let securityManager: SecurityManager;
let updateManager: UpdateManager;
let activityLogger: ActivityLogger;

// Отключаем аппаратное ускорение для большей стабильности
app.disableHardwareAcceleration();

// Единственный экземпляр приложения
const gotTheLock = app.requestSingleInstanceLock();

if (!gotTheLock) {
  app.quit();
} else {
  app.on('second-instance', () => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
  });

  app.whenReady().then(async () => {
    console.log('[SubCloudy] App is ready, initializing...');
    console.log('[SubCloudy] __dirname:', __dirname);
    console.log('[SubCloudy] app.isPackaged:', app.isPackaged);
    
    // Инициализация менеджеров
    authManager = new AuthManager(store);
    securityManager = new SecurityManager();
    activityLogger = new ActivityLogger(store, authManager);
    serviceManager = new ServiceManager(authManager, securityManager, store, activityLogger);
    updateManager = new UpdateManager(store);

    console.log('[SubCloudy] Managers initialized');
    
    // Обновляем токен на desktop токен если пользователь авторизован
    if (authManager.isAuthenticated()) {
      authManager.refreshDesktopToken().catch((error) => {
        console.error('[SubCloudy] Failed to refresh desktop token:', error);
      });
    }

    // Настройка сессии
    const defaultSession = session.defaultSession;
    
    // Блокировка опасных разрешений
    defaultSession.setPermissionRequestHandler((webContents, permission, callback) => {
      const allowedPermissions = ['notifications'];
      callback(allowedPermissions.includes(permission));
    });

    // Создание главного окна
    console.log('[SubCloudy] Creating main window...');
    mainWindow = await createMainWindow(store);
    console.log('[SubCloudy] Main window created');

    // Регистрация IPC handlers
    console.log('[SubCloudy] Setting up IPC handlers...');
    setupIPCHandlers();
    console.log('[SubCloudy] IPC handlers ready');

    // Запуск периодической проверки обновлений (с задержкой 5 секунд)
    if (app.isPackaged) {
      setTimeout(() => {
        updateManager.checkForUpdates();
        updateManager.startPeriodicCheck();
      }, 5000);
    }

    app.on('activate', async () => {
      if (BrowserWindow.getAllWindows().length === 0) {
        mainWindow = await createMainWindow(store);
      }
    });
  });

  app.on('window-all-closed', () => {
    console.log('[SubCloudy] All windows closed');
    if (process.platform !== 'darwin') {
      app.quit();
    }
  });

  app.on('will-quit', () => {
    console.log('[SubCloudy] App will quit');
  });
}

// Настройка обработчиков IPC
function setupIPCHandlers() {
  // Аутентификация
  ipcMain.handle('auth:login', async (_, credentials) => {
    return await authManager.login(credentials);
  });

  ipcMain.handle('auth:logout', async () => {
    return await authManager.logout();
  });

  ipcMain.handle('auth:getUser', async () => {
    return await authManager.getUser();
  });

  ipcMain.handle('auth:isAuthenticated', async () => {
    return authManager.isAuthenticated();
  });

  ipcMain.handle('auth:getToken', async () => {
    return authManager.getToken();
  });

  ipcMain.handle('auth:updateProfile', async (_, data) => {
    return await authManager.updateProfile(data);
  });

  ipcMain.handle('auth:toggleAutoRenew', async (_, subscriptionId) => {
    return await authManager.toggleAutoRenew(subscriptionId);
  });

  // OAuth handlers (заглушки - нужно реализовать позже)
  ipcMain.handle('auth:loginWithGoogle', async () => {
    console.log('[IPC] Google OAuth not implemented yet');
    return { success: false, error: 'Google OAuth not implemented in desktop app yet' };
  });

  ipcMain.handle('auth:loginWithTelegram', async () => {
    console.log('[IPC] Telegram OAuth not implemented yet');
    return { success: false, error: 'Telegram OAuth not implemented in desktop app yet' };
  });

  // Сервисы
  ipcMain.handle('services:getAvailable', async () => {
    return await serviceManager.getAvailableServices();
  });

  ipcMain.handle('services:launch', async (_, serviceId: number) => {
    if (!mainWindow) return { success: false, error: 'Main window not found' };
    return await serviceManager.launchService(serviceId, mainWindow);
  });

  ipcMain.handle('services:stop', async (_, sessionId: string) => {
    return await serviceManager.stopService(sessionId);
  });

  ipcMain.handle('services:stopAll', async () => {
    return await serviceManager.stopAllSessions();
  });

  // Утилиты
  ipcMain.handle('app:getVersion', () => {
    return app.getVersion();
  });

  ipcMain.handle('app:minimize', () => {
    mainWindow?.minimize();
  });

  ipcMain.handle('app:maximize', () => {
    if (mainWindow?.isMaximized()) {
      mainWindow.unmaximize();
    } else {
      mainWindow?.maximize();
    }
  });

  ipcMain.handle('app:close', () => {
    mainWindow?.close();
  });

  // Обновления
  ipcMain.handle('updater:checkForUpdates', async (_, force = false) => {
    return await updateManager.checkForUpdates(force);
  });

  ipcMain.handle('updater:downloadUpdate', async () => {
    return await updateManager.downloadUpdate();
  });

  ipcMain.handle('updater:installUpdate', async () => {
    return await updateManager.installUpdate();
  });

  ipcMain.handle('updater:getStatus', () => {
    return updateManager.getUpdateInfo();
  });

  ipcMain.handle('updater:getSettings', () => {
    return updateManager.getSettings();
  });

  ipcMain.handle('updater:updateSettings', (_, settings) => {
    updateManager.updateSettings(settings);
    return { success: true };
  });

  // Подписка на изменения статуса обновлений для отправки в renderer
  updateManager.onStatusChange((status) => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('updater:status-changed', status);
    }
  });

  // История активности
  ipcMain.handle('activity:getHistory', (_, filters) => {
    return activityLogger.getHistory(filters);
  });

  ipcMain.handle('activity:exportHistory', (_, format, filters) => {
    if (format === 'csv') {
      return activityLogger.exportToCSV(filters);
    } else if (format === 'json') {
      return activityLogger.exportToJSON(filters);
    }
    throw new Error('Invalid export format');
  });

  ipcMain.handle('activity:clearHistory', () => {
    activityLogger.clearHistory();
    return { success: true };
  });

  ipcMain.handle('activity:sync', async () => {
    return await activityLogger.syncWithBackend();
  });

  ipcMain.handle('activity:getSyncStats', () => {
    return activityLogger.getSyncStats();
  });
}

// Обработка необработанных ошибок
process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
  if (app.isReady()) {
    dialog.showErrorBox(
      'Фатальная ошибка приложения',
      `Произошла непредвиденная ошибка: ${error.message}\n\nПожалуйста, свяжитесь с поддержкой.`
    );
  }
});

process.on('unhandledRejection', (reason: any) => {
  console.error('Unhandled Rejection:', reason);
  if (app.isReady()) {
    dialog.showErrorBox(
      'Ошибка асинхронной операции',
      `Запрос завершился неудачей: ${reason?.message || reason}\n\nПриложение продолжит работу, но некоторые функции могут быть недоступны.`
    );
  }
});

