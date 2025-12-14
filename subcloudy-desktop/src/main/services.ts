import { BrowserView, BrowserWindow, session as electronSession } from 'electron';
import { AuthManager } from './auth';
import { SecurityManager } from './security';
import { createServiceWindow } from './windows/serviceWindow';
import Store from 'electron-store';

interface Cookie {
  name: string;
  value: string;
  domain?: string;
  path?: string;
  secure?: boolean;
  httpOnly?: boolean;
  sameSite?: 'unspecified' | 'no_restriction' | 'lax' | 'strict';
  expirationDate?: number;
}

interface Service {
  id: number;
  code: string;
  name: string;
  logo: string;
  amount: number;
  params: {
    link?: string;
    icon?: string;
    title?: string;
  };
}

interface ServiceSession {
  id: string;
  serviceId: number;
  window: BrowserWindow;
  userId: number;
  startedAt: Date;
  lastActivity: Date;
}

export class ServiceManager {
  private authManager: AuthManager;
  private securityManager: SecurityManager;
  private store: Store;
  private activeSessions: Map<string, ServiceSession> = new Map();
  private inactivityTimeout = 30 * 60 * 1000; // 30 минут

  constructor(authManager: AuthManager, securityManager: SecurityManager, store: Store) {
    this.authManager = authManager;
    this.securityManager = securityManager;
    this.store = store;

    // Периодическая проверка неактивных сессий
    setInterval(() => this.checkInactiveSessions(), 60 * 1000); // каждую минуту
  }

  /**
   * Получение списка доступных сервисов для пользователя
   */
  async getAvailableServices(): Promise<Service[]> {
    try {
      const api = this.authManager.getApiInstance();
      const response = await api.get('/services');
      
      const user = this.authManager.getCurrentUser();
      if (!user) return [];

      // Фильтруем только активные сервисы пользователя
      const services = response.data as Service[];
      return services.filter(service => 
        user.active_services && user.active_services.includes(service.id)
      );
    } catch (error) {
      console.error('[Services] Error fetching services:', error);
      return [];
    }
  }

  /**
   * Запуск сервиса в изолированном BrowserView
   */
  async launchService(serviceId: number, mainWindow: BrowserWindow): Promise<{ success: boolean; error?: string; sessionId?: string }> {
    try {
      const user = this.authManager.getCurrentUser();
      if (!user) {
        return { success: false, error: 'User not authenticated' };
      }

      // Проверка доступа к сервису
      if (!user.active_services?.includes(serviceId)) {
        return { success: false, error: 'Service not available for user' };
      }

      console.log('[Services] Getting service account from backend...');
      
      // Получаем данные аккаунта с cookies от backend
      const api = this.authManager.getApiInstance();
      
      let accountResponse;
      try {
        accountResponse = await api.post('/desktop/service-url', { service_id: serviceId });
      } catch (apiError: any) {
        throw apiError;
      }
      
      const accountData = accountResponse.data;
      
      console.log('[Services] Account data received:', {
        service_name: accountData.service_name,
        profile_id: accountData.profile_id,
        cookies_count: accountData.credentials?.cookies?.length || 0
      });

      if (!accountData.success || !accountData.service_url) {
        return { success: false, error: accountData.error || 'Failed to get service account' };
      }

      const { service_url: serviceUrl, service_name: serviceName, profile_id, credentials } = accountData;

      // Создаем изолированную сессию для этого сервиса
      const partitionName = `service-${user.id}-${serviceId}-${Date.now()}`;
      const serviceSession = electronSession.fromPartition(partitionName, { cache: false });

      console.log('[Services] Created isolated session:', partitionName);

      // КЛЮЧЕВОЙ МОМЕНТ: Загружаем cookies в session для автологина
      if (credentials?.cookies && Array.isArray(credentials.cookies) && credentials.cookies.length > 0) {
        console.log('[Services] Loading cookies into session...');
        await this.loadCookiesIntoSession(serviceSession, credentials.cookies, serviceUrl);
        console.log('[Services] Cookies loaded successfully!');
      } else {
        console.warn('[Services] No cookies found in credentials. Service will open without autologin.');
      }

      console.log('[Services] Creating service window...');

      // Создаем отдельное окно для сервиса с предзагруженными cookies
      const serviceWindow = createServiceWindow(
        serviceId,
        serviceName || `Service ${serviceId}`,
        serviceUrl,
        user.id,
        serviceSession // Передаем session с cookies
      );

      // Создание записи сессии
      const sessionId = `session-${user.id}-${serviceId}-${Date.now()}`;
      const sessionData: ServiceSession = {
        id: sessionId,
        serviceId,
        window: serviceWindow,
        userId: user.id,
        startedAt: new Date(),
        lastActivity: new Date()
      };

      this.activeSessions.set(sessionId, sessionData);

      // Отслеживание закрытия окна
      serviceWindow.on('closed', () => {
        console.log('[Services] Service window closed:', sessionId);
        this.activeSessions.delete(sessionId);
      });

      // Отслеживание активности
      serviceWindow.webContents.on('did-navigate', () => {
        const session = this.activeSessions.get(sessionId);
        if (session) {
          session.lastActivity = new Date();
        }
      });

      // Логирование запуска
      this.logServiceActivity(user.id, serviceId, 'session_started');

      console.log('[Services] Service launched successfully!');
      return { success: true, sessionId };
    } catch (error: any) {
      console.error('[Services] Error launching service:', error);
      return { 
        success: false, 
        error: error.message || error.response?.data?.message || 'Failed to launch service' 
      };
    }
  }

  /**
   * Остановка сервиса
   */
  async stopService(sessionId: string): Promise<{ success: boolean }> {
    const session = this.activeSessions.get(sessionId);
    if (!session) {
      return { success: false };
    }

    try {
      // Логирование остановки
      this.logServiceActivity(session.userId, session.serviceId, 'session_stopped');

      // Закрытие окна
      if (session.window && !session.window.isDestroyed()) {
        session.window.close();
      }

      // Очистка сессии
      await this.securityManager.clearSession(`service:${session.userId}:${session.serviceId}`);

      // Удаление из активных
      this.activeSessions.delete(sessionId);

      return { success: true };
    } catch (error) {
      console.error('[Services] Error stopping service:', error);
      return { success: false };
    }
  }

  /**
   * Остановка всех активных сессий
   */
  async stopAllSessions(): Promise<{ success: boolean }> {
    try {
      for (const [sessionId] of this.activeSessions) {
        await this.stopService(sessionId);
      }
      return { success: true };
    } catch (error) {
      console.error('[Services] Error stopping all sessions:', error);
      return { success: false };
    }
  }


  /**
   * Проверка неактивных сессий
   */
  private checkInactiveSessions() {
    const now = Date.now();
    for (const [sessionId, session] of this.activeSessions) {
      const inactive = now - session.lastActivity.getTime();
      if (inactive > this.inactivityTimeout) {
        console.log(`[Services] Session ${sessionId} inactive for ${inactive}ms, closing...`);
        this.stopService(sessionId);
      }
    }
  }

  /**
   * Логирование активности (можно отправлять на backend)
   */
  private async logServiceActivity(userId: number, serviceId: number, action: string) {
    try {
      const api = this.authManager.getApiInstance();
      await api.post('/desktop/log', {
        user_id: userId,
        service_id: serviceId,
        action,
        timestamp: Date.now()
      });
    } catch (error) {
      console.error('[Services] Failed to log activity:', error);
    }
  }

  /**
   * Получение активных сессий
   */
  getActiveSessions(): ServiceSession[] {
    return Array.from(this.activeSessions.values());
  }

  /**
   * Загрузка cookies в Electron session для автологина
   */
  private async loadCookiesIntoSession(
    session: Electron.Session, 
    cookies: Cookie[], 
    serviceUrl: string
  ): Promise<void> {
    console.log('[Services] Starting to load', cookies.length, 'cookies...');
    
    // Получаем базовый домен из URL для cookies
    let baseDomain = '';
    try {
      const url = new URL(serviceUrl);
      baseDomain = url.hostname;
    } catch (e) {
      console.error('[Services] Invalid service URL:', serviceUrl);
      baseDomain = 'localhost';
    }

    let successCount = 0;
    let errorCount = 0;

    for (const cookie of cookies) {
      try {
        // Проверяем, является ли cookie с префиксом __Host- или __Secure-
        const isHostPrefix = cookie.name.startsWith('__Host-');
        const isSecurePrefix = cookie.name.startsWith('__Secure-');
        
        // Очищаем domain от точки в начале для URL (но сохраняем для cookie)
        const domainForUrl = (cookie.domain || baseDomain).replace(/^\./, '');
        const cookieUrl = `https://${domainForUrl}`;
        
        const electronCookie: Electron.CookiesSetDetails = {
          url: cookieUrl,
          name: cookie.name,
          value: cookie.value,
          path: cookie.path || '/',
          secure: cookie.secure !== false,
          httpOnly: cookie.httpOnly !== false,
        };

        // Для cookies с префиксом __Host- НЕ указываем domain (требование спецификации)
        // Для cookies с префиксом __Secure- можно указать domain, но он должен быть secure
        if (!isHostPrefix) {
          electronCookie.domain = cookie.domain || baseDomain;
        }
        // Для __Host- также убеждаемся, что path = '/' и secure = true
        if (isHostPrefix) {
          electronCookie.path = '/';
          electronCookie.secure = true;
        }

        // Добавляем expirationDate если есть
        if (cookie.expirationDate) {
          electronCookie.expirationDate = cookie.expirationDate;
        }

        // Добавляем sameSite если есть
        if (cookie.sameSite) {
          electronCookie.sameSite = cookie.sameSite;
        }

        // Устанавливаем cookie в session
        await session.cookies.set(electronCookie);
        successCount++;
        
        console.log('[Services] Cookie loaded:', cookie.name, 'for domain:', cookie.domain || baseDomain);
      } catch (error: any) {
        errorCount++;
        console.error('[Services] Failed to set cookie:', cookie.name, error.message);
      }
    }

    console.log(`[Services] Cookies loaded: ${successCount} success, ${errorCount} failed`);
    
    if (successCount === 0 && cookies.length > 0) {
      throw new Error('Failed to load any cookies');
    }
  }

}


