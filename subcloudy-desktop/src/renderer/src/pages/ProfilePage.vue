<template>
  <div class="profile-page min-h-screen bg-[#fafafa] dark:bg-gray-900">
    <!-- Animated gradient background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
      <div class="animated-gradient absolute w-[120vw] h-[120vh] opacity-40 blur-[70px]" />
    </div>

    <div class="relative z-10">
      <!-- Back to services -->
      <div class="max-w-2xl mx-auto px-4 pt-8">
        <button @click="goBack" class="back-button">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          {{ safeT('services.backToServices') }}
        </button>
      </div>

      <!-- Page Content -->
      <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-light text-gray-900 dark:text-white">
            {{ safeT('profile.title') }}
          </h1>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6 bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl">
          <!-- Name -->
          <div class="space-y-2">
            <label class="text-sm text-gray-700 dark:text-gray-300" for="name">
              {{ safeT('profile.name') }}
            </label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <input
                id="name"
                v-model="formData.name"
                type="text"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :placeholder="safeT('profile.namePlaceholder')"
                required
              />
            </div>
            <p v-if="errors.name" class="text-red-500 text-sm">
              {{ errors.name[0] }}
            </p>
          </div>

          <!-- Email -->
          <div class="space-y-2">
            <label class="text-sm text-gray-700 dark:text-gray-300" for="email">
              {{ safeT('profile.email') }}
            </label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <input
                id="email"
                v-model="formData.email"
                type="email"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :placeholder="safeT('profile.emailPlaceholder')"
                required
              />
            </div>
            <p v-if="errors.email" class="text-red-500 text-sm">
              {{ errors.email[0] }}
            </p>
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label class="text-sm text-gray-700 dark:text-gray-300" for="password">
              {{ safeT('profile.password') }}
            </label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <input
                id="password"
                v-model="formData.password"
                type="password"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :placeholder="safeT('profile.passwordPlaceholder')"
                autocomplete="new-password"
              />
            </div>
            <p v-if="errors.password" class="text-red-500 text-sm">
              {{ errors.password[0] }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div v-if="formData.password" class="space-y-2">
            <label class="text-sm text-gray-700 dark:text-gray-300" for="password_confirmation">
              {{ safeT('profile.confirmPassword') }}
            </label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <input
                id="password_confirmation"
                v-model="formData.password_confirmation"
                type="password"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :placeholder="safeT('profile.confirmPasswordPlaceholder')"
                autocomplete="new-password"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full text-white py-3 rounded-lg bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
          >
            <span v-if="!loading">{{ safeT('profile.saveButton') }}</span>
            <span v-else class="flex items-center gap-2 justify-center">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ safeT('profile.saving') }}
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

// Безопасная функция для переводов с обработкой ошибок linked format
const safeT = (key: string, ...args: any[]) => {
  try {
    const result = t(key, ...args);
    // Заменяем экранированный @@ обратно на @ для отображения
    const finalResult = typeof result === 'string' ? result.replace(/@@/g, '@') : result;
    return finalResult;
  } catch (error: any) {
    console.warn(`[ProfilePage] Translation error for key "${key}":`, error);
    return key; // Возвращаем ключ в случае ошибки
  }
};

const formData = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const errors = ref<Record<string, string[]>>({});

onMounted(() => {
  console.log('[ProfilePage] Mounted');
  console.log('[ProfilePage] User:', authStore.user);
  
  if (authStore.user) {
    formData.name = authStore.user.name || '';
    formData.email = authStore.user.email || '';
  }
});

function goBack() {
  router.push('/services');
}

async function handleSubmit() {
  console.log('[ProfilePage] Submitting form...');
  loading.value = true;
  errors.value = {};
  
  try {
    // Валидация паролей
    if (formData.password) {
      if (formData.password !== formData.password_confirmation) {
        errors.value = { password: [t('profile.passwordMismatch') || 'Passwords do not match'] };
        loading.value = false;
        return;
      }
    }

    const payload: any = {
      name: formData.name,
      email: formData.email
    };
    
    if (formData.password) {
      payload.password = formData.password;
      payload.password_confirmation = formData.password_confirmation;
    }
    
    console.log('[ProfilePage] Sending update:', payload);
    
    // Отправка на backend через electronAPI
    const result = await window.electronAPI.auth.updateProfile(payload);
    
    console.log('[ProfilePage] Update result:', result);
    
    if (result.success) {
      alert(t('profile.success'));
      formData.password = '';
      formData.password_confirmation = '';
      
      // Обновить данные пользователя
      await authStore.checkAuth();
    } else {
      errors.value = result.errors || {};
      alert(result.error || t('profile.error'));
    }
  } catch (error: any) {
    console.error('[ProfilePage] Error:', error);
    alert(t('profile.error'));
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.animated-gradient {
  background: linear-gradient(
    120deg,
    rgba(255, 106, 0, 0.35) 10%,
    rgba(255, 0, 204, 0.55) 35%,
    rgba(0, 170, 255, 0.75) 70%,
    rgba(0, 123, 255, 0.45) 90%
  );
  animation: gradientMove 30s ease-in-out infinite;
}

@keyframes gradientMove {
  0%, 100% { transform: translate(-18%, -18%) rotate(0deg) scale(1); }
  25% { transform: translate(-10%, -22%) rotate(20deg) scale(1.03); }
  50% { transform: translate(8%, -12%) rotate(40deg) scale(0.98); }
  75% { transform: translate(-12%, 8%) rotate(25deg) scale(1.02); }
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  color: #374151;
  transition: color 0.2s;
}

.dark .back-button {
  color: #d1d5db;
}

.back-button:hover {
  color: #2563eb;
}

.dark .back-button:hover {
  color: #60a5fa;
}
</style>
