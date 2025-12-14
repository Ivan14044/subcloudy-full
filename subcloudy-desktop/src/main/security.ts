import { BrowserView, WebContents, session, Session } from 'electron';

export class SecurityManager {
  private activeSessions: Map<string, Session> = new Map();

  /**
   * Создает изолированную сессию для сервиса
   */
  createIsolatedSession(userId: number, serviceId: number): Session {
    const partitionName = `service:${userId}:${serviceId}:${Date.now()}`;
    const serviceSession = session.fromPartition(partitionName, { cache: false });

    // Блокировка доступа к cookies через JavaScript
    serviceSession.protocol.registerStringProtocol('cookie', () => {
      throw new Error('Cookie access denied');
    });

    // Настройка блокировки опасных запросов
    serviceSession.webRequest.onBeforeRequest((details, callback) => {
      const url = details.url.toLowerCase();
      
      // Блокировка попыток доступа к API аутентификации
      const blockedPatterns = [
        '/api/auth/',
        '/api/login',
        '/api/register',
        '/cookies',
        '/credential'
      ];

      const shouldBlock = blockedPatterns.some(pattern => url.includes(pattern));
      
      if (shouldBlock) {
        console.warn(`[Security] Blocked request: ${details.url}`);
        callback({ cancel: true });
      } else {
        callback({});
      }
    });

    // Блокировка загрузки расширений и devtools
    serviceSession.webRequest.onBeforeRequest({ urls: ['*://*/*'] }, (details, callback) => {
      if (details.url.includes('chrome-extension://')) {
        callback({ cancel: true });
      } else {
        callback({});
      }
    });

    this.activeSessions.set(partitionName, serviceSession);
    return serviceSession;
  }

  /**
   * Применяет политики безопасности к BrowserView
   */
  applySecurityPolicies(view: BrowserView, userId: number) {
    const webContents = view.webContents;

    // Отключение DevTools полностью
    webContents.closeDevTools();
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
      // F12, Ctrl+Shift+I, Ctrl+Shift+C, Ctrl+Shift+J
      if (input.key === 'F12' ||
          (input.control && input.shift && ['I', 'C', 'J', 'K'].includes(input.key.toUpperCase())) ||
          (input.control && ['U', 'S'].includes(input.key.toUpperCase()))) {
        event.preventDefault();
      }

      // Блокировка копирования/вставки в критичных сценариях
      if (input.control && ['C', 'V', 'X'].includes(input.key.toUpperCase())) {
        // Можно настроить более гибкую логику
        console.log('[Security] Copy/Paste/Cut attempt detected');
      }
    });

    // Добавление watermark после загрузки страницы
    webContents.on('did-finish-load', () => {
      this.addWatermark(webContents, userId);
    });

    // Мониторинг навигации
    webContents.on('will-navigate', (event, url) => {
      console.log(`[Security] Navigation to: ${url}`);
      // Можно добавить ограничения на навигацию
    });

    // Блокировка попыток получить удаленный доступ
    // Примечание: remote модуль удален в новых версиях Electron, 
    // поэтому эти события больше не нужны
  }

  /**
   * Добавляет невидимый watermark с ID пользователя
   */
  private addWatermark(webContents: WebContents, userId: number) {
    const watermarkScript = `
      (function() {
        try {
          // Создаем невидимый watermark
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

          // Защита от удаления watermark
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

    webContents.executeJavaScript(watermarkScript).catch((err) => {
      console.error('[Security] Failed to add watermark:', err);
    });
  }

  /**
   * Инжект дополнительных скриптов безопасности
   */
  injectSecurityScripts(webContents: WebContents) {
    const securityScript = `
      (function() {
        // Блокировка доступа к cookies
        Object.defineProperty(document, 'cookie', {
          get: function() { return ''; },
          set: function() { return true; },
          configurable: false
        });

        // Блокировка localStorage и sessionStorage (опционально)
        // Storage.prototype.getItem = function() { return null; };
        // Storage.prototype.setItem = function() { return; };
        
        // Отключение console методов для затруднения debugging
        const noop = function() {};
        ['log', 'warn', 'error', 'info', 'debug'].forEach(method => {
          if (window.console) window.console[method] = noop;
        });

        // Детектор DevTools (базовый)
        let devtoolsOpen = false;
        const detector = /./;
        detector.toString = function() {
          devtoolsOpen = true;
          window.location.href = 'about:blank';
        };
        
        setInterval(() => {
          devtoolsOpen = false;
          console.log('%c', detector);
          if (devtoolsOpen) {
            window.location.href = 'about:blank';
          }
        }, 1000);

        // Блокировка правой кнопки мыши
        document.addEventListener('contextmenu', e => e.preventDefault(), true);
        
        // Блокировка выделения текста (опционально)
        // document.addEventListener('selectstart', e => e.preventDefault(), true);

        console.log('[SubCloudy Security] Protection enabled');
      })();
    `;

    webContents.executeJavaScript(securityScript).catch((err) => {
      console.error('[Security] Failed to inject security scripts:', err);
    });
  }

  /**
   * Очистка сессии
   */
  async clearSession(partitionName: string) {
    const sess = this.activeSessions.get(partitionName);
    if (sess) {
      await sess.clearCache();
      await sess.clearStorageData();
      this.activeSessions.delete(partitionName);
    }
  }

  /**
   * Очистка всех активных сессий
   */
  async clearAllSessions() {
    for (const [name, sess] of this.activeSessions) {
      await sess.clearCache();
      await sess.clearStorageData();
    }
    this.activeSessions.clear();
  }
}

