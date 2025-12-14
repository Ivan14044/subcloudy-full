import { BrowserWindow, shell, app } from 'electron';
import { join } from 'path';
import Store from 'electron-store';

export async function createMainWindow(store: Store): Promise<BrowserWindow> {
  // Восстановление размеров окна из настроек
  const savedBounds = store.get('window_bounds') as any;
  const defaultBounds = {
    width: 1200,
    height: 800,
    x: undefined,
    y: undefined
  };

  const bounds = savedBounds || defaultBounds;

  const mainWindow = new BrowserWindow({
    width: bounds.width,
    height: bounds.height,
    x: bounds.x,
    y: bounds.y,
    minWidth: 900,
    minHeight: 600,
    frame: true,
    backgroundColor: '#1a1a1a',
    show: false, // Показываем после загрузки
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: join(__dirname, '../preload/index.js'),
      webSecurity: true,
      sandbox: true
    },
    icon: join(__dirname, '../../resources/icons/icon.png')
  });

  // Сохранение размеров окна при изменении
  mainWindow.on('resize', () => {
    const bounds = mainWindow.getBounds();
    store.set('window_bounds', bounds);
  });

  mainWindow.on('move', () => {
    const bounds = mainWindow.getBounds();
    store.set('window_bounds', bounds);
  });

  // Открытие внешних ссылок в браузере
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (url.startsWith('http://') || url.startsWith('https://')) {
      shell.openExternal(url);
    }
    return { action: 'deny' };
  });

  // Загрузка интерфейса
  const isDev = !app.isPackaged;
  
  if (isDev) {
    // В режиме разработки загружаем с Vite dev server
    console.log('[MainWindow] Development mode, loading from Vite...');
    
    try {
      // Даём Vite время запуститься
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Пробуем подключиться к Vite
      await mainWindow.loadURL('http://localhost:5175');
      console.log('[MainWindow] Loaded from http://localhost:5175');
      
      // Включаем DevTools для отладки в dev режиме
      mainWindow.webContents.openDevTools();
    } catch (error) {
      console.error('[MainWindow] Failed to load from Vite:', error);
      // Fallback на production build если Vite недоступен
      const rendererPath = join(__dirname, '../renderer/index.html');
      console.log('[MainWindow] Fallback to:', rendererPath);
      await mainWindow.loadFile(rendererPath);
    }
  } else {
    // В продакшене загружаем из собранных файлов
    console.log('[MainWindow] Production mode, loading from file...');
    const rendererPath = join(__dirname, '../renderer/index.html');
    console.log('[MainWindow] Loading from:', rendererPath);
    await mainWindow.loadFile(rendererPath);
  }

  // Показываем окно после загрузки
  mainWindow.once('ready-to-show', () => {
    console.log('[MainWindow] Window ready to show');
    mainWindow.show();
    mainWindow.focus();
  });

  // Обработка закрытия окна
  mainWindow.on('close', (e) => {
    console.log('[MainWindow] Window closing');
  });

  // Обработка ошибок загрузки
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
    console.error('[MainWindow] Failed to load:', errorCode, errorDescription);
  });

  return mainWindow;
}
