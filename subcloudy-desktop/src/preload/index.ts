import { contextBridge, ipcRenderer } from 'electron';

// API для безопасного взаимодействия между Renderer и Main процессами
const electronAPI = {
  // Аутентификация
  auth: {
    login: (credentials: { email: string; password: string }) => 
      ipcRenderer.invoke('auth:login', credentials),
    logout: () => 
      ipcRenderer.invoke('auth:logout'),
    getUser: () => 
      ipcRenderer.invoke('auth:getUser'),
    isAuthenticated: () => 
      ipcRenderer.invoke('auth:isAuthenticated'),
    // OAuth методы
    loginWithGoogle: () => 
      ipcRenderer.invoke('auth:loginWithGoogle'),
    loginWithTelegram: () => 
      ipcRenderer.invoke('auth:loginWithTelegram'),
    // Управление профилем и подписками
    updateProfile: (data: any) =>
      ipcRenderer.invoke('auth:updateProfile', data),
    toggleAutoRenew: (subscriptionId: number) =>
      ipcRenderer.invoke('auth:toggleAutoRenew', subscriptionId)
  },

  // Сервисы
  services: {
    getAvailable: () => 
      ipcRenderer.invoke('services:getAvailable'),
    launch: (serviceId: number) => 
      ipcRenderer.invoke('services:launch', serviceId),
    stop: (sessionId: string) => 
      ipcRenderer.invoke('services:stop', sessionId),
    stopAll: () => 
      ipcRenderer.invoke('services:stopAll')
  },

  // Управление приложением
  app: {
    getVersion: () => 
      ipcRenderer.invoke('app:getVersion'),
    minimize: () => 
      ipcRenderer.invoke('app:minimize'),
    maximize: () => 
      ipcRenderer.invoke('app:maximize'),
    close: () => 
      ipcRenderer.invoke('app:close')
  },

  // Подписка на события
  on: (channel: string, callback: (...args: any[]) => void) => {
    const validChannels = [
      'session-expired',
      'service-error',
      'auth-error'
    ];
    
    if (validChannels.includes(channel)) {
      ipcRenderer.on(channel, (_, ...args) => callback(...args));
    }
  },

  // Отписка от событий
  off: (channel: string, callback: (...args: any[]) => void) => {
    ipcRenderer.removeListener(channel, callback);
  }
};

// Экспорт API в window
contextBridge.exposeInMainWorld('electronAPI', electronAPI);

// TypeScript типы для глобального window
declare global {
  interface Window {
    electronAPI: typeof electronAPI;
  }
}


