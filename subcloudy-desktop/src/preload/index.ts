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

  // Обновления
  updater: {
    checkForUpdates: (force = false) => 
      ipcRenderer.invoke('updater:checkForUpdates', force),
    downloadUpdate: () => 
      ipcRenderer.invoke('updater:downloadUpdate'),
    installUpdate: () => 
      ipcRenderer.invoke('updater:installUpdate'),
    getStatus: () => 
      ipcRenderer.invoke('updater:getStatus'),
    getSettings: () => 
      ipcRenderer.invoke('updater:getSettings'),
    updateSettings: (settings: any) => 
      ipcRenderer.invoke('updater:updateSettings', settings),
    onStatusChange: (callback: (status: any) => void) => {
      ipcRenderer.on('updater:status-changed', (_, status) => callback(status));
      return () => ipcRenderer.removeAllListeners('updater:status-changed');
    }
  },

  // История активности
  activity: {
    getHistory: (filters?: any) => 
      ipcRenderer.invoke('activity:getHistory', filters),
    exportHistory: (format: 'csv' | 'json', filters?: any) => 
      ipcRenderer.invoke('activity:exportHistory', format, filters),
    clearHistory: () => 
      ipcRenderer.invoke('activity:clearHistory'),
    sync: () => 
      ipcRenderer.invoke('activity:sync'),
    getSyncStats: () => 
      ipcRenderer.invoke('activity:getSyncStats')
  },

  // Подписка на события
  on: (channel: string, callback: (...args: any[]) => void) => {
    const validChannels = [
      'session-expired',
      'service-error',
      'auth-error',
      'updater:status-changed'
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


