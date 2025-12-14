<template>
  <div class="services-page min-h-screen bg-[#fafafa] dark:bg-gray-900">
    <!-- Animated gradient background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div
        :class="[
          'animated-gradient absolute w-[120vw] h-[120vh]',
          isDark ? 'opacity-60 blur-[80px]' : 'opacity-40 blur-[70px]'
        ]"
      />
    </div>

    <div class="relative z-10">
      <!-- Header (identical to website) -->
      <header class="text-gray-900 dark:text-white">
        <div class="relative z-100 max-w-7xl w-full mx-auto px-2 sm:px-4 py-2 flex justify-center">
          <div
            class="bg-indigo-soft-200/90 dark:bg-gray-800/90 rounded-full px-4 py-1 flex items-center transition-all duration-1000 dashboard border !border-indigo-soft-400 dark:!border-gray-700 w-full gap-2 justify-between"
          >
            <!-- Logo + SubCloudy text (left side) -->
            <div class="flex items-center sm:gap-2 w-[32px] sm:w-[150px] xl:!w-[160px] cursor-pointer">
              <img
                :src="logo"
                alt="SubCloudy"
                class="!w-[29px] sm:w-8 sm:h-8 object-contain rounded-full spin-slow-reverse"
              />
              <span class="text-xl font-semibold whitespace-nowrap hidden sm:flex">
                SubCloudy
              </span>
            </div>

            <!-- Right side: Theme toggle + Language selector + User menu -->
            <div class="w-full d-flex justify-between sm:pl-6">
              <div class="flex items-center gap-1">
                <ThemeToggle />
                <LanguageSelector />
              </div>
              <div class="relative flex items-center gap-1">
                <UserMenu />
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Loading -->
        <div v-if="servicesStore.loading && servicesStore.services.length === 0" class="loading-container">
          <div class="spinner-large"></div>
          <p class="text-gray-600 dark:text-gray-300 mt-4">{{ $t('services.loading') }}</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!servicesStore.loading && servicesStore.services.length === 0" class="empty-state">
          <div class="empty-icon">📦</div>
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">{{ $t('services.noServices') }}</h2>
          <p class="text-gray-600 dark:text-gray-300 mb-6">{{ $t('services.noServicesDesc') }}</p>
          <button class="btn-primary" @click="openWebsite">
            {{ $t('services.browseServices') }}
          </button>
        </div>

        <!-- Services Grid -->
        <div v-else>
          <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
              {{ $t('services.activeServices') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-300">
              {{ $t('services.clickToLaunch') }}
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-8 xl:gap-6">
            <ServiceCard
              v-for="service in servicesStore.services"
              :key="service.id"
              :service="service"
              @launch="handleLaunch"
            />
          </div>
        </div>

        <!-- Error -->
        <div v-if="servicesStore.error" class="error-banner">
          {{ servicesStore.error }}
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth';
import { useServicesStore } from '../stores/services';
import ServiceCard from '../components/services/ServiceCard.vue';
import LanguageSelector from '../components/LanguageSelector.vue';
import UserMenu from '../components/UserMenu.vue';
import ThemeToggle from '../components/ThemeToggle.vue';
import { useDarkMode } from '../composables/useDarkMode';
import logo from '../assets/logo.webp';

const router = useRouter();
const authStore = useAuthStore();
const servicesStore = useServicesStore();
const { isDark } = useDarkMode();
const { t } = useI18n();

onMounted(async () => {
  console.log('[ServicesPage] Mounted, fetching services...');
  await servicesStore.fetchServices();
  console.log('[ServicesPage] Services fetched:', servicesStore.services.length);
});

async function handleLaunch(serviceId: number) {
  console.log('[ServicesPage] Launching service:', serviceId);
  const result = await servicesStore.launchService(serviceId);
  
  if (!result.success) {
    alert(result.error || t('services.launchError'));
  }
}

// handleLogout теперь в UserMenu компоненте

function openWebsite() {
  console.log('Opening website...');
  // Можно добавить открытие через shell
}
</script>

<style scoped>
.services-page {
  min-height: 100vh;
}

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
  0%,
  100% {
    transform: translate(-18%, -18%) rotate(0deg) scale(1);
  }
  25% {
    transform: translate(-10%, -22%) rotate(20deg) scale(1.03);
  }
  50% {
    transform: translate(8%, -12%) rotate(40deg) scale(0.98);
  }
  75% {
    transform: translate(-12%, 8%) rotate(25deg) scale(1.02);
  }
}

/* Header styles (matching website) */
.dashboard {
  height: 44px !important;
}

.indigo-soft-200\/90 {
  background-color: rgba(224, 231, 255, 0.9);
}

.indigo-soft-400 {
  border-color: rgba(165, 180, 252, 1);
}

.dark .indigo-soft-200\/90 {
  background-color: rgba(31, 41, 55, 0.9);
}

.dark .indigo-soft-400 {
  border-color: rgba(75, 85, 99, 1);
}

.z-100 {
  z-index: 100;
}

.d-flex {
  display: flex;
}

.justify-between {
  justify-content: space-between;
}

.spin-slow-reverse {
  animation: spin-reverse 8s linear infinite;
}

@keyframes spin-reverse {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}

.btn-logout {
  @apply px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors;
}

.btn-primary {
  @apply px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(59, 130, 246, 0.2);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  text-align: center;
  padding: 40px 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.error-banner {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 16px 24px;
  background: rgba(239, 68, 68, 0.95);
  color: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
  max-width: 500px;
  text-align: center;
  animation: slideUp 0.3s ease-out;
  z-index: 1000;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translate(-50%, 20px);
  }
  to {
    opacity: 1;
    transform: translate(-50%, 0);
  }
}
</style>
