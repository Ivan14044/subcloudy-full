import { defineStore } from 'pinia';
import { ref } from 'vue';

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

export const useServicesStore = defineStore('services', () => {
  const services = ref<Service[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const activeSession = ref<string | null>(null);

  async function fetchServices() {
    loading.value = true;
    error.value = null;

    try {
      const data = await window.electronAPI.services.getAvailable();
      
      // Исправляем пути к логотипам - добавляем baseURL
      const baseURL = 'http://127.0.0.1:8000';
      services.value = data.map((service: any) => ({
        ...service,
        logo: service.logo?.startsWith('http') 
          ? service.logo 
          : baseURL + service.logo
      }));
      
      console.log('[ServicesStore] Services loaded:', services.value.length);
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch services';
      console.error('Fetch services error:', err);
    } finally {
      loading.value = false;
    }
  }

  async function launchService(serviceId: number) {
    loading.value = true;
    error.value = null;

    try {
      const result = await window.electronAPI.services.launch(serviceId);
      
      if (result.success && result.sessionId) {
        activeSession.value = result.sessionId;
        return { success: true };
      } else {
        error.value = result.error || 'Failed to launch service';
        return { success: false, error: error.value };
      }
    } catch (err: any) {
      error.value = err.message || 'An error occurred';
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  }

  async function stopService() {
    if (!activeSession.value) return;

    try {
      await window.electronAPI.services.stop(activeSession.value);
      activeSession.value = null;
    } catch (err) {
      console.error('Stop service error:', err);
    }
  }

  async function stopAllServices() {
    try {
      await window.electronAPI.services.stopAll();
      activeSession.value = null;
    } catch (err) {
      console.error('Stop all services error:', err);
    }
  }

  return {
    services,
    loading,
    error,
    activeSession,
    fetchServices,
    launchService,
    stopService,
    stopAllServices
  };
});


