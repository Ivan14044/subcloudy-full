import { defineStore } from 'pinia';
import { ref } from 'vue';

interface User {
  id: number;
  email: string;
  name: string;
  active_services: number[];
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const isAuthenticated = ref(false);
  const initialized = ref(false);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function checkAuth() {
    try {
      initialized.value = false;
      const authenticated = await window.electronAPI.auth.isAuthenticated();
      isAuthenticated.value = authenticated;

      if (authenticated) {
        const userData = await window.electronAPI.auth.getUser();
        user.value = userData;
      }
    } catch (err: any) {
      console.error('Check auth error:', err);
      isAuthenticated.value = false;
      user.value = null;
    } finally {
      initialized.value = true;
    }
  }

  async function login(email: string, password: string) {
    loading.value = true;
    error.value = null;

    try {
      const result = await window.electronAPI.auth.login({ email, password });

      if (result.success && result.user) {
        user.value = result.user;
        isAuthenticated.value = true;
        return { success: true };
      } else {
        error.value = result.error || 'Login failed';
        return { success: false, error: error.value };
      }
    } catch (err: any) {
      error.value = err.message || 'An error occurred during login';
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    loading.value = true;
    error.value = null;

    try {
      await window.electronAPI.auth.logout();
      user.value = null;
      isAuthenticated.value = false;
      return { success: true };
    } catch (err: any) {
      error.value = err.message || 'Logout failed';
      return { success: false, error: error.value };
    } finally {
      loading.value = false;
    }
  }

  return {
    user,
    isAuthenticated,
    initialized,
    loading,
    error,
    checkAuth,
    login,
    logout
  };
});


