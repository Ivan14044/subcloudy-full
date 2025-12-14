import { BrowserWindow, Session } from 'electron';
import { join } from 'path';

/**
 * Создание защищенного окна для сервиса с предзагруженными cookies
 */
export function createServiceWindow(
  serviceId: number, 
  serviceName: string,
  serviceUrl: string,
  userId: number,
  electronSession: Session // Принимаем session с уже загруженными cookies
): BrowserWindow {
  console.log('[ServiceWindow] Creating window for:', serviceName);
  console.log('[ServiceWindow] URL:', serviceUrl);
  console.log('[ServiceWindow] Using session with cookies');

  const serviceWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 800,
    minHeight: 600,
    title: serviceName,
    backgroundColor: '#ffffff',
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      // Preload НЕ нужен для serviceWindow - он для сторонних сайтов
      // preload: join(__dirname, '../preload/index.js'),
      devTools: false, // Отключаем DevTools для безопасности
      webSecurity: true,
      sandbox: false, // Отключаем sandbox для поддержки аудио API (микрофон, диктовка)
      session: electronSession, // Используем переданную session с cookies
      // Явно разрешаем доступ к медиа-устройствам
      allowRunningInsecureContent: false,
      experimentalFeatures: false
    }
  });

  // Настройка разрешений для микрофона, камеры и хранилища
  electronSession.setPermissionRequestHandler((webContents, permission, callback, details) => {
    const timestamp = new Date().toISOString();
    console.log('═══════════════════════════════════════════════════');
    console.log(`[${timestamp}] [ServiceWindow] PERMISSION REQUEST`);
    console.log('[ServiceWindow] Permission type:', permission);
    console.log('[ServiceWindow] WebContents ID:', webContents.id);
    console.log('[ServiceWindow] URL:', webContents.getURL());
    if (details) {
      console.log('[ServiceWindow] Details:', JSON.stringify(details, null, 2));
    }
    
    // Разрешаем важные разрешения для работы сервисов
    // 'media' включает микрофон и камеру
    const allowedPermissions = ['media', 'notifications', 'persistent-storage'];
    
    if (allowedPermissions.includes(permission)) {
      console.log(`[ServiceWindow] ✅ GRANTING PERMISSION: ${permission}`);
      callback(true);
    } else {
      // Остальные разрешения по умолчанию отклоняем
      console.log('[ServiceWindow] ❌ DENYING PERMISSION:', permission);
      callback(false);
    }
    console.log('═══════════════════════════════════════════════════');
  });

  // Получаем webContents для обработки событий
  const webContents = serviceWindow.webContents;

  // Применяем политики безопасности
  applyServiceSecurity(serviceWindow, userId);

  // Загружаем URL сервиса
  serviceWindow.loadURL(serviceUrl).catch((error) => {
    console.error('[ServiceWindow] Failed to load URL:', error);
  });

  // Показываем окно после загрузки
  serviceWindow.once('ready-to-show', () => {
    console.log('[ServiceWindow] Window ready, showing...');
    serviceWindow.show();
    serviceWindow.focus();
  });

  // ДЕТАЛЬНОЕ ЛОГИРОВАНИЕ ВСЕХ СОБЫТИЙ
  
  // Логирование загрузки страницы
  webContents.on('did-start-loading', () => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] 🔄 Page loading started`);
  });

  webContents.on('did-finish-load', () => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] ✅ Page loaded successfully`);
  });

  webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL) => {
    console.error(`[${new Date().toISOString()}] [ServiceWindow] ❌ Page failed to load`);
    console.error('[ServiceWindow] Error code:', errorCode);
    console.error('[ServiceWindow] Error description:', errorDescription);
    console.error('[ServiceWindow] URL:', validatedURL);
  });

  // Обработка краша renderer процесса
  webContents.on('render-process-gone', (event, details) => {
    console.error('╔═══════════════════════════════════════════════════╗');
    console.error(`║  RENDER PROCESS CRASHED - ${new Date().toISOString()}  ║`);
    console.error('╚═══════════════════════════════════════════════════╝');
    console.error('[ServiceWindow] Reason:', details.reason);
    console.error('[ServiceWindow] Exit code:', details.exitCode);
    
    // Пытаемся перезагрузить страницу
    if (details.reason !== 'clean-exit') {
      console.log('[ServiceWindow] 🔄 Attempting to reload in 1 second...');
      setTimeout(() => {
        if (!serviceWindow.isDestroyed()) {
          serviceWindow.reload();
        }
      }, 1000);
    }
  });

  // Обработка зависания
  webContents.on('unresponsive', () => {
    console.error('╔═══════════════════════════════════════════════════╗');
    console.error(`║  PAGE UNRESPONSIVE - ${new Date().toISOString()}    ║`);
    console.error('╚═══════════════════════════════════════════════════╝');
  });

  // Обработка восстановления
  webContents.on('responsive', () => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] ✅ Page became responsive again`);
  });

  // Логирование ВСЕХ сообщений консоли (не только ошибок)
  webContents.on('console-message', (event, level, message, line, sourceId) => {
    const levelNames = ['VERBOSE', 'INFO', 'WARNING', 'ERROR'];
    const levelName = levelNames[level] || 'UNKNOWN';
    const emoji = level >= 2 ? '❌' : level === 1 ? '⚠️' : 'ℹ️';
    
    console.log(`[${new Date().toISOString()}] [Browser Console] ${emoji} [${levelName}] ${message}`);
    if (line && sourceId) {
      console.log(`  └─ at line ${line} in ${sourceId}`);
    }
  });

  // Логирование медиа-событий
  webContents.on('media-started-playing', () => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] 🎵 Media started playing`);
  });

  webContents.on('media-paused', () => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] ⏸️ Media paused`);
  });

  // Логирование запросов доступа к медиа-устройствам
  webContents.on('select-bluetooth-device', (event, deviceList, callback) => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] 📱 Bluetooth device selection requested`);
    event.preventDefault();
    callback('');
  });


  // Логирование навигации
  webContents.on('will-navigate', (event, url) => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] 🔗 Navigating to: ${url}`);
  });

  webContents.on('did-navigate', (event, url) => {
    console.log(`[${new Date().toISOString()}] [ServiceWindow] ✅ Navigated to: ${url}`);
  });

  // Защита от случайного закрытия (игнорируем первые 3 секунды)
  let canClose = false;
  setTimeout(() => {
    canClose = true;
  }, 3000);

  serviceWindow.on('close', (event) => {
    if (!canClose) {
      console.log('[ServiceWindow] Close prevented - window just opened');
      event.preventDefault();
      canClose = true; // Разрешаем закрыть при следующей попытке
    } else {
      console.log('[ServiceWindow] Closing window for:', serviceName);
    }
  });

  // Логирование при закрытии
  serviceWindow.on('closed', () => {
    console.log('[ServiceWindow] Window closed for:', serviceName);
  });

  return serviceWindow;
}

/**
 * Применение политик безопасности к окну сервиса
 */
function applyServiceSecurity(window: BrowserWindow, userId: number) {
  const webContents = window.webContents;

  // Блокировка DevTools
  webContents.on('devtools-opened', () => {
    webContents.closeDevTools();
  });

  // Блокировка контекстного меню
  webContents.on('context-menu', (e) => {
    e.preventDefault();
  });

  // Блокировка открытия новых окон
  webContents.setWindowOpenHandler(() => {
    return { action: 'deny' };
  });

  // Блокировка опасных комбинаций клавиш
  webContents.on('before-input-event', (event, input) => {
    // F12, Ctrl+Shift+I, Ctrl+Shift+C, Ctrl+Shift+J, Ctrl+U
    if (
      input.key === 'F12' ||
      (input.control && input.shift && ['I', 'C', 'J', 'K'].includes(input.key.toUpperCase())) ||
      (input.control && input.key.toUpperCase() === 'U')
    ) {
      event.preventDefault();
    }
  });

  // Добавление watermark и аудио-патча после загрузки
  webContents.on('did-finish-load', () => {
    addWatermark(webContents, userId);
    patchAudioContext(webContents);
  });

  console.log('[ServiceWindow] Security policies applied');
}

/**
 * Добавление водяного знака с ID пользователя
 */
function addWatermark(webContents: any, userId: number) {
  const watermarkScript = `
    (function() {
      try {
        const watermark = document.createElement('div');
        watermark.id = 'sc-watermark-${Date.now()}';
        watermark.style.cssText = \`
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%) rotate(-45deg);
          opacity: 0.03;
          font-size: 120px;
          font-weight: bold;
          color: #000;
          pointer-events: none;
          z-index: 999999;
          user-select: none;
        \`;
        watermark.textContent = 'User ${userId}';
        document.body.appendChild(watermark);

        // Защита от удаления
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            mutation.removedNodes.forEach((node) => {
              if (node.id && node.id.startsWith('sc-watermark-')) {
                document.body.appendChild(watermark);
              }
            });
          });
        });

        observer.observe(document.body, { childList: true, subtree: true });
      } catch (e) {
        console.error('[Watermark] Error:', e);
      }
    })();
  `;

  webContents.executeJavaScript(watermarkScript).catch((err: any) => {
    console.error('[ServiceWindow] Failed to add watermark:', err);
  });
}

/**
 * Отключение ScriptProcessorNode для предотвращения крашей аудио
 * и принуждение использования AudioWorklet (более стабильного API)
 */
function patchAudioContext(webContents: any) {
  const script = `
    (() => {
      try {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC || AC.__subcloudyPatched) {
          return;
        }
        AC.__subcloudyPatched = true;

        if (AC.prototype.createScriptProcessor) {
          AC.prototype.createScriptProcessor = function() {
            console.warn('[SubCloudy] ScriptProcessorNode disabled. Forcing AudioWorklet.');
            const error = new Error('ScriptProcessorNode disabled for stability reasons');
            error.name = 'NotSupportedError';
            throw error;
          };
        }
      } catch (error) {
        console.error('[SubCloudy] Failed to patch AudioContext:', error);
      }
    })();
  `;

  webContents.executeJavaScript(script, true).catch((err: any) => {
    console.error('[ServiceWindow] Failed to patch AudioContext:', err);
  });
}

