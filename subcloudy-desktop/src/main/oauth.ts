import { BrowserWindow } from 'electron';

/**
 * Результат OAuth авторизации
 */
export interface OAuthResult {
  success: boolean;
  token?: string;
  user?: any;
  error?: string;
}

/**
 * Менеджер OAuth авторизации для desktop приложения
 */
export class OAuthManager {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  /**
   * OAuth авторизация через Google
   */
  async loginWithGoogle(): Promise<OAuthResult> {
    return new Promise((resolve) => {
      console.log('[OAuth] Starting Google OAuth flow...');

      const authWindow = new BrowserWindow({
        width: 500,
        height: 700,
        show: true,
        center: true,
        title: 'Google Authorization',
        webPreferences: {
          nodeIntegration: false,
          contextIsolation: true,
          sandbox: false,
          devTools: false
        }
      });

      // Добавляем параметр для идентификации desktop приложения
      const authUrl = `${this.baseURL}/auth/google?from_desktop=1`;
      
      console.log('[OAuth] Opening Google auth URL:', authUrl);
      authWindow.loadURL(authUrl);

      let resolved = false;

      // Отслеживаем навигацию для обнаружения callback
      authWindow.webContents.on('did-navigate', async (event, url) => {
        console.log('[OAuth] Navigation detected:', url);
        
        // Проверяем попали ли мы на callback страницу
        if (url.includes('/auth/google/callback') || url.includes('/auth/callback')) {
          console.log('[OAuth] Callback page detected! Extracting token...');
          
          try {
            // Ждём немного чтобы JavaScript на странице выполнился
            await new Promise(resolve => setTimeout(resolve, 500));

            // Извлекаем данные из страницы
            const result: any = await authWindow.webContents.executeJavaScript(`
              (function() {
                try {
                  // Ищем данные в скриптах
                  const scripts = document.querySelectorAll('script');
                  
                  for (const script of scripts) {
                    const content = script.textContent || '';
                    
                    // Ищем паттерн с токеном
                    if (content.includes('token:') && content.includes('success:')) {
                      // Извлекаем токен через регулярное выражение
                      const tokenMatch = content.match(/token:\\s*'([^']+)'/);
                      const successMatch = content.match(/success:\\s*(true|false)/);
                      
                      if (tokenMatch && successMatch) {
                        // Пытаемся найти user данные
                        const userMatch = content.match(/user:\\s*({[^}]+}|@json\\([^)]+\\))/);
                        
                        return {
                          success: successMatch[1] === 'true',
                          token: tokenMatch[1],
                          user: userMatch ? userMatch[1] : null,
                          source: 'script_extraction'
                        };
                      }
                    }
                  }
                  
                  // Если не нашли в скриптах, проверяем body (может быть JSON)
                  const bodyText = document.body.textContent || '';
                  if (bodyText.trim().startsWith('{')) {
                    return JSON.parse(bodyText);
                  }
                  
                  return { 
                    success: false, 
                    error: 'Token not found in page',
                    html: document.body.innerHTML.substring(0, 500)
                  };
                } catch (e) {
                  return { 
                    success: false, 
                    error: 'Extraction error: ' + e.message 
                  };
                }
              })();
            `);

            console.log('[OAuth] Extraction result:', {
              success: result.success,
              hasToken: !!result.token,
              error: result.error
            });

            if (!resolved) {
              resolved = true;
              authWindow.close();
              resolve(result);
            }
          } catch (error: any) {
            console.error('[OAuth] Failed to extract token:', error);
            if (!resolved) {
              resolved = true;
              authWindow.close();
              resolve({ 
                success: false, 
                error: 'Failed to extract token: ' + error.message 
              });
            }
          }
        }
      });

      // Обработка ошибок загрузки
      authWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
        console.error('[OAuth] Page failed to load:', errorCode, errorDescription);
        if (!resolved) {
          resolved = true;
          authWindow.close();
          resolve({ 
            success: false, 
            error: `Failed to load: ${errorDescription}` 
          });
        }
      });

      // Обработка закрытия окна без авторизации
      authWindow.on('closed', () => {
        console.log('[OAuth] Auth window closed by user');
        if (!resolved) {
          resolved = true;
          resolve({ 
            success: false, 
            error: 'User closed authorization window' 
          });
        }
      });
    });
  }

  /**
   * OAuth авторизация через Telegram
   */
  async loginWithTelegram(): Promise<OAuthResult> {
    return new Promise((resolve) => {
      console.log('[OAuth] Starting Telegram OAuth flow...');

      const authWindow = new BrowserWindow({
        width: 500,
        height: 700,
        show: true,
        center: true,
        title: 'Telegram Authorization',
        webPreferences: {
          nodeIntegration: false,
          contextIsolation: true,
          sandbox: false,
          devTools: false
        }
      });

      // Telegram widget обычно находится на странице логина
      const authUrl = `${this.baseURL}/login?from_desktop=1#telegram`;
      
      console.log('[OAuth] Opening Telegram auth URL:', authUrl);
      authWindow.loadURL(authUrl);

      let resolved = false;

      // Отслеживаем навигацию для Telegram callback
      authWindow.webContents.on('did-navigate', async (event, url) => {
        console.log('[OAuth] Telegram navigation:', url);
        
        if (url.includes('/auth/telegram/callback')) {
          console.log('[OAuth] Telegram callback detected!');
          
          try {
            await new Promise(resolve => setTimeout(resolve, 500));

            // Telegram обычно возвращает JSON напрямую
            const result: any = await authWindow.webContents.executeJavaScript(`
              (function() {
                try {
                  const bodyText = document.body.textContent || '';
                  
                  // Пытаемся распарсить как JSON
                  if (bodyText.trim().startsWith('{')) {
                    return JSON.parse(bodyText);
                  }
                  
                  // Иначе ищем в скриптах аналогично Google
                  const scripts = document.querySelectorAll('script');
                  for (const script of scripts) {
                    const content = script.textContent || '';
                    if (content.includes('token:')) {
                      const tokenMatch = content.match(/token:\\s*'([^']+)'/);
                      if (tokenMatch) {
                        return {
                          success: true,
                          token: tokenMatch[1]
                        };
                      }
                    }
                  }
                  
                  return { success: false, error: 'Token not found' };
                } catch (e) {
                  return { success: false, error: e.message };
                }
              })();
            `);

            console.log('[OAuth] Telegram result:', result);

            if (!resolved) {
              resolved = true;
              authWindow.close();
              resolve(result);
            }
          } catch (error: any) {
            console.error('[OAuth] Telegram extraction error:', error);
            if (!resolved) {
              resolved = true;
              authWindow.close();
              resolve({ success: false, error: error.message });
            }
          }
        }
      });

      authWindow.on('closed', () => {
        if (!resolved) {
          resolved = true;
          resolve({ 
            success: false, 
            error: 'User closed authorization window' 
          });
        }
      });
    });
  }
}




