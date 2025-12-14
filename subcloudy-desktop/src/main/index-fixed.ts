import { app, BrowserWindow, ipcMain, session } from 'electron';
import { join } from 'path';
import { AuthManager } from './auth';
import { ServiceManager } from './services';
import { SecurityManager } from './security';
import { OAuthManager } from './oauth';
import Store from 'electron-store';

console.log('[SubCloudy] Starting application...');

// Исправление крашей аудио на Windows (Exit code -1073741819)
app.commandLine.appendSwitch('disable-features', 'AudioServiceSandbox');
app.commandLine.appendSwitch('enable-features', 'AudioServiceOutOfProcess');
app.commandLine.appendSwitch('disable-gpu-sandbox');
console.log('[SubCloudy] Audio crash fix flags applied');

// Инициализация защищенного хранилища
const store = new Store({
  encryptionKey: 'subcloudy-secure-key-2024',
  name: 'subcloudy-config'
});

let mainWindow: BrowserWindow | null = null;
let authManager: AuthManager;
let serviceManager: ServiceManager;
let securityManager: SecurityManager;
let oauthManager: OAuthManager;

// Отключаем аппаратное ускорение для большей стабильности
app.disableHardwareAcceleration();

// Единственный экземпляр приложения
const gotTheLock = app.requestSingleInstanceLock();

if (!gotTheLock) {
  console.log('[SubCloudy] Another instance is running, quitting...');
  app.quit();
} else {
  app.on('second-instance', () => {
    console.log('[SubCloudy] Second instance detected, focusing main window');
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
    const apiUrl = process.env.API_URL || 'http://127.0.0.1:8000/api';
    authManager = new AuthManager(store);
    securityManager = new SecurityManager();
    serviceManager = new ServiceManager(authManager, securityManager, store);
    oauthManager = new OAuthManager(apiUrl.replace('/api', '')); // Убираем /api для OAuth URL

    console.log('[SubCloudy] Managers initialized');

    // Регистрация IPC handlers ДО создания окна
    console.log('[SubCloudy] Setting up IPC handlers...');
    setupIPCHandlers();
    console.log('[SubCloudy] IPC handlers ready');

    // Настройка сессии
    const defaultSession = session.defaultSession;
    
    // Блокировка опасных разрешений
    defaultSession.setPermissionRequestHandler((webContents, permission, callback) => {
      const allowedPermissions = ['notifications'];
      callback(allowedPermissions.includes(permission));
    });

    // Создание главного окна
    console.log('[SubCloudy] Creating main window...');
    
    const preloadPath = join(__dirname, '../preload/index.js');
    console.log('[SubCloudy] Preload path:', preloadPath);

    mainWindow = new BrowserWindow({
      width: 1200,
      height: 800,
      minWidth: 900,
      minHeight: 600,
      frame: true,
      backgroundColor: '#1a1a1a',
      show: true,
      webPreferences: {
        nodeIntegration: false,
        contextIsolation: true,
        preload: preloadPath,
        webSecurity: true,
        sandbox: false // Для корректной работы preload
      }
    });

    console.log('[SubCloudy] Main window created');

    // Путь к HTML
    const htmlPath = join(__dirname, '../renderer/index.html');
    console.log('[SubCloudy] Loading HTML from:', htmlPath);

    try {
      await mainWindow.loadFile(htmlPath);
      console.log('[SubCloudy] HTML loaded successfully!');
    } catch (error) {
      console.error('[SubCloudy] Error loading HTML:', error);
    }

    // Открываем DevTools для отладки
    mainWindow.webContents.openDevTools();

    app.on('activate', async () => {
      if (BrowserWindow.getAllWindows().length === 0) {
        console.log('[SubCloudy] No windows, creating new one');
      }
    });
  });

  app.on('window-all-closed', () => {
    console.log('[SubCloudy] All windows closed');
    
    // НЕ квитим приложение! Пользователь может открыть другой сервис
    // Главное окно остаётся в фоне
    console.log('[SubCloudy] Keeping app running (user can launch another service)');
    
    // На macOS приложения не квитятся при закрытии окон
    // На Windows/Linux - тоже оставляем работать
  });

  app.on('will-quit', () => {
    console.log('[SubCloudy] App will quit');
  });
}

// Настройка обработчиков IPC
function setupIPCHandlers() {
  // Аутентификация
  ipcMain.handle('auth:login', async (_, credentials) => {
    console.log('[IPC] auth:login called');
    return await authManager.login(credentials);
  });

  ipcMain.handle('auth:logout', async () => {
    console.log('[IPC] auth:logout called');
    return await authManager.logout();
  });

  ipcMain.handle('auth:getUser', async () => {
    console.log('[IPC] auth:getUser called');
    return await authManager.getUser();
  });

  // OAuth авторизация
  ipcMain.handle('auth:loginWithGoogle', async () => {
    console.log('[IPC] auth:loginWithGoogle called');
    try {
      const result = await oauthManager.loginWithGoogle();
      
      if (result.success && result.token) {
        // Сохраняем токен через authManager
        await authManager.setTokenDirectly(result.token, result.user);
        console.log('[IPC] Google OAuth successful, user logged in');
      }
      
      return result;
    } catch (error: any) {
      console.error('[IPC] Google OAuth error:', error);
      return { success: false, error: error.message };
    }
  });

  ipcMain.handle('auth:loginWithTelegram', async () => {
    console.log('[IPC] auth:loginWithTelegram called');
    try {
      const result = await oauthManager.loginWithTelegram();
      
      if (result.success && result.token) {
        await authManager.setTokenDirectly(result.token, result.user);
        console.log('[IPC] Telegram OAuth successful, user logged in');
      }
      
      return result;
    } catch (error: any) {
      console.error('[IPC] Telegram OAuth error:', error);
      return { success: false, error: error.message };
    }
  });

  ipcMain.handle('auth:isAuthenticated', async () => {
    console.log('[IPC] auth:isAuthenticated called');
    return authManager.isAuthenticated();
  });

  // Сервисы
  ipcMain.handle('services:getAvailable', async () => {
    console.log('[IPC] services:getAvailable called');
    return await serviceManager.getAvailableServices();
  });

  ipcMain.handle('services:launch', async (_, serviceId: number) => {
    console.log('[IPC] services:launch called:', serviceId);
    if (!mainWindow) return { success: false, error: 'Main window not found' };
    return await serviceManager.launchService(serviceId, mainWindow);
  });

  ipcMain.handle('services:stop', async (_, sessionId: string) => {
    console.log('[IPC] services:stop called:', sessionId);
    return await serviceManager.stopService(sessionId);
  });

  ipcMain.handle('services:stopAll', async () => {
    console.log('[IPC] services:stopAll called');
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
}

// Обработка необработанных ошибок
process.on('uncaughtException', (error) => {
  console.error('[SubCloudy] Uncaught Exception:', error);
});

process.on('unhandledRejection', (reason) => {
  console.error('[SubCloudy] Unhandled Rejection:', reason);
});

console.log('[SubCloudy] Main script loaded');

