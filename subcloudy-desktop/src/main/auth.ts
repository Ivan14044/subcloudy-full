import axios, { AxiosInstance } from 'axios';
import Store from 'electron-store';

interface LoginCredentials {
  email: string;
  password: string;
}

interface User {
  id: number;
  email: string;
  name: string;
  active_services: number[];
}

interface AuthResponse {
  success: boolean;
  user?: User;
  token?: string;
  error?: string;
}

export class AuthManager {
  private store: Store;
  private api: AxiosInstance;
  private token: string | null = null;
  private user: User | null = null;

  constructor(store: Store) {
    this.store = store;
    
    // API base URL из переменных окружения или дефолт
    const apiUrl = process.env.API_URL || 'http://127.0.0.1:8000/api';
    
    console.log('[AuthManager] API URL:', apiUrl);
    
    this.api = axios.create({
      baseURL: apiUrl,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      timeout: 10000
    });

    // Загрузка сохраненного токена (синхронно для немедленного использования)
    this.loadStoredAuthSync();

    // Добавление токена к каждому запросу
    this.api.interceptors.request.use((config) => {
      if (this.token) {
        config.headers.Authorization = `Bearer ${this.token}`;
      }
      return config;
    });

    // Обработка ошибок авторизации
    this.api.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          this.clearAuth();
        }
        return Promise.reject(error);
      }
    );
  }

  private loadStoredAuthSync() {
    const storedToken = this.store.get('auth_token') as string | undefined;
    const storedUser = this.store.get('auth_user') as User | undefined;

    if (storedToken && storedUser) {
      this.token = storedToken;
      this.user = storedUser;
      // Устанавливаем токен для немедленного использования
      this.api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
    }
  }

  /**
   * Обновление токена на desktop токен (вызывается после инициализации)
   */
  async refreshDesktopToken(): Promise<void> {
    if (!this.token || !this.user) return;

    try {
      const desktopAuthResponse = await this.api.post('/desktop/auth');
      const desktopData = desktopAuthResponse.data;
      
      if (desktopData.success && desktopData.token && desktopData.user) {
        this.saveAuth(desktopData.token, desktopData.user);
        console.log('[AuthManager] Desktop token refreshed');
      }
    } catch (error: any) {
      console.warn('[AuthManager] Failed to refresh desktop token:', error?.message);
    }
  }

  private async loadStoredAuth() {
    const storedToken = this.store.get('auth_token') as string | undefined;
    const storedUser = this.store.get('auth_user') as User | undefined;

    // Этот метод больше не используется, оставлен для совместимости
    // Используем loadStoredAuthSync + refreshDesktopToken
  }

  private saveAuth(token: string, user: User) {
    this.token = token;
    this.user = user;
    this.store.set('auth_token', token);
    this.store.set('auth_user', user);
  }

  /**
   * Установка токена напрямую (для OAuth)
   */
  async setTokenDirectly(token: string, user?: any): Promise<void> {
    console.log('[AuthManager] Setting token directly from OAuth');
    this.token = token;
    
    // Если user не передан, получаем его с backend
    if (!user) {
      try {
        this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        const response = await this.api.get('/user');
        user = response.data;
      } catch (error) {
        console.error('[AuthManager] Failed to fetch user after OAuth:', error);
        throw error;
      }
    }
    
    this.saveAuth(token, user);
    console.log('[AuthManager] OAuth token saved successfully');
  }

  private clearAuth() {
    this.token = null;
    this.user = null;
    this.store.delete('auth_token');
    this.store.delete('auth_user');
  }

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      console.log('[AuthManager] Attempting login to:', this.api.defaults.baseURL + '/login');
      console.log('[AuthManager] Credentials:', { email: credentials.email, password: '***' });
      
      const response = await this.api.post('/login', credentials);
      console.log('[AuthManager] Response status:', response.status);
      console.log('[AuthManager] Response data:', response.data);
      
      const { token, user } = response.data;

      if (token && user) {
        // ВАЖНО: Для desktop приложения нужен специальный токен с правами desktop:access
        // Используем обычный токен временно для получения desktop токена
        this.token = token;
        this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        try {
          // Получаем desktop токен с правильными правами
          const desktopAuthResponse = await this.api.post('/desktop/auth');
          const desktopData = desktopAuthResponse.data;
          
          if (desktopData.success && desktopData.token && desktopData.user) {
            // Используем desktop токен и данные пользователя
            this.saveAuth(desktopData.token, desktopData.user);
            console.log('[AuthManager] Desktop auth successful, user:', desktopData.user.email);
            return { success: true, user: desktopData.user, token: desktopData.token };
          }
        } catch (desktopError: any) {
          console.error('[AuthManager] Desktop auth failed, using regular token:', desktopError);
          // Fallback: используем обычный токен если desktop auth не удался
          this.saveAuth(token, user);
          return { success: true, user, token };
        }
        
        // Fallback если desktop auth не вернул данные
        this.saveAuth(token, user);
        console.log('[AuthManager] Login successful (fallback), user:', user.email);
        return { success: true, user, token };
      }

      console.error('[AuthManager] Invalid response - no token or user');
      return { success: false, error: 'Invalid response from server' };
    } catch (error: any) {
      console.error('[AuthManager] Login error:', error);
      console.error('[AuthManager] Error response:', error.response?.data);
      console.error('[AuthManager] Error status:', error.response?.status);
      console.error('[AuthManager] Error message:', error.message);
      
      const errorMessage = error.response?.data?.message 
        || error.response?.statusText
        || error.message 
        || 'Login failed';
      
      return { 
        success: false, 
        error: errorMessage
      };
    }
  }

  async logout(): Promise<{ success: boolean }> {
    try {
      if (this.token) {
        // Logout использует GET, а не POST
        await this.api.get('/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      this.clearAuth();
    }
    return { success: true };
  }

  async getUser(): Promise<User | null> {
    if (!this.token) return null;

    try {
      const response = await this.api.get('/user');
      this.user = response.data;
      this.store.set('auth_user', this.user);
      return this.user;
    } catch (error) {
      console.error('Get user error:', error);
      return this.user;
    }
  }

  isAuthenticated(): boolean {
    return !!(this.token && this.user);
  }

  getToken(): string | null {
    return this.token;
  }

  getCurrentUser(): User | null {
    return this.user;
  }

  getApiInstance(): AxiosInstance {
    return this.api;
  }

  /**
   * Обновление профиля пользователя
   */
  async updateProfile(data: any): Promise<{ success: boolean; user?: User; error?: string; errors?: any }> {
    try {
      console.log('[AuthManager] Updating profile:', data);
      
      // Правильный endpoint: POST /user (не PUT /user/profile)
      const response = await this.api.post('/user', data);
      console.log('[AuthManager] Profile updated:', response.data);
      
      // Обновляем данные пользователя
      if (response.data.user) {
        this.user = response.data.user;
        this.store.set('auth_user', this.user);
      }
      
      // Обновляем полные данные пользователя
      await this.getUser();
      
      return { success: true, user: this.user || undefined };
    } catch (error: any) {
      console.error('[AuthManager] Profile update error:', error);
      console.error('[AuthManager] Error details:', error.response?.data);
      
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to update profile',
        errors: error.response?.data?.errors || {}
      };
    }
  }

  /**
   * Переключение автопродления подписки
   */
  async toggleAutoRenew(subscriptionId: number): Promise<{ success: boolean; error?: string }> {
    try {
      console.log('[AuthManager] Toggling auto-renew for subscription:', subscriptionId);
      
      // Правильный endpoint: POST /toggle-auto-renew
      const response = await this.api.post('/toggle-auto-renew', {
        subscription_id: subscriptionId
      });
      console.log('[AuthManager] Auto-renew toggled:', response.data);
      
      // Обновляем данные пользователя чтобы получить актуальные подписки
      await this.getUser();
      
      return { success: true };
    } catch (error: any) {
      console.error('[AuthManager] Toggle auto-renew error:', error);
      console.error('[AuthManager] Error details:', error.response?.data);
      
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to toggle auto-renew'
      };
    }
  }
}

