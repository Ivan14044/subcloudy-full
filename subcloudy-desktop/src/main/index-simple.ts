import { app, BrowserWindow, ipcMain } from 'electron';
import { join } from 'path';
import Store from 'electron-store';

console.log('[Simple] Starting application...');

// Инициализация store
const store = new Store({
  encryptionKey: 'subcloudy-secure-key-2024',
  name: 'subcloudy-config'
});

let mainWindow: BrowserWindow | null = null;

app.whenReady().then(async () => {
  console.log('[Simple] App ready!');
  console.log('[Simple] __dirname:', __dirname);
  console.log('[Simple] Current directory:', process.cwd());

  const preloadPath = join(__dirname, '../preload/index.js');
  console.log('[Simple] Preload path:', preloadPath);

  mainWindow = new BrowserWindow({
    width: 1200,
    height: 800,
    backgroundColor: '#1a1a1a',
    show: true,
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: preloadPath,
      sandbox: false // Отключаем sandbox для упрощения отладки
    }
  });

  // Регистрируем базовые IPC handlers
  setupBasicIPCHandlers();

  console.log('[Simple] Window created');

  // Путь к index.html
  const htmlPath = join(__dirname, '../renderer/index.html');
  console.log('[Simple] Loading HTML from:', htmlPath);

  try {
    await mainWindow.loadFile(htmlPath);
    console.log('[Simple] HTML loaded successfully!');
  } catch (error) {
    console.error('[Simple] Error loading HTML:', error);
  }

  mainWindow.on('closed', () => {
    console.log('[Simple] Window closed');
    mainWindow = null;
  });

  mainWindow.webContents.openDevTools();
});

app.on('window-all-closed', () => {
  console.log('[Simple] All windows closed, quitting...');
  app.quit();
});

app.on('quit', () => {
  console.log('[Simple] App quit');
});

// Базовые IPC handlers для тестирования
function setupBasicIPCHandlers() {
  console.log('[Simple] Setting up IPC handlers...');

  // Mock auth handlers
  ipcMain.handle('auth:login', async (_, credentials) => {
    console.log('[Simple] Login called:', credentials);
    return { 
      success: false, 
      error: 'Backend not connected. This is a test version.' 
    };
  });

  ipcMain.handle('auth:logout', async () => {
    console.log('[Simple] Logout called');
    return { success: true };
  });

  ipcMain.handle('auth:getUser', async () => {
    console.log('[Simple] Get user called');
    return null;
  });

  ipcMain.handle('auth:isAuthenticated', async () => {
    console.log('[Simple] Is authenticated called');
    return false;
  });

  // Mock services handlers
  ipcMain.handle('services:getAvailable', async () => {
    console.log('[Simple] Get services called');
    return [];
  });

  ipcMain.handle('services:launch', async (_, serviceId) => {
    console.log('[Simple] Launch service called:', serviceId);
    return { success: false, error: 'Test version' };
  });

  ipcMain.handle('services:stop', async (_, sessionId) => {
    console.log('[Simple] Stop service called:', sessionId);
    return { success: true };
  });

  ipcMain.handle('services:stopAll', async () => {
    console.log('[Simple] Stop all services called');
    return { success: true };
  });

  // Mock app handlers
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

  console.log('[Simple] IPC handlers ready');
}

console.log('[Simple] Script loaded');

