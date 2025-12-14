<template>
    <div ref="dropdownRef" class="d-flex gap-5 relative">
        <button
            class="px-2 px-lg-3 d-flex py-2 h-[32px] text-base leading-4 items-center rounded-lg hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 group"
            @click="toggleDropdown"
        >
            <!-- User icon SVG -->
            <svg 
                class="w-5 h-5 flex-shrink-0 text-gray-900 dark:text-white" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>

            <span class="flex items-center sm:pl-2 min-w-0">
                <span
                    v-if="authStore.user?.email"
                    class="hidden md:inline-block truncate whitespace-nowrap overflow-hidden font-normal text-[15px] text-gray-900 dark:text-white max-w-[120px] xl:max-w-[180px] 2xl:max-w-[250px]"
                    :title="authStore.user.email"
                >
                    {{ authStore.user.email }}
                </span>

                <!-- ChevronDown icon -->
                <svg
                    :class="[
                        'xl:ml-1 w-4 h-4 flex-shrink-0 transition-transform duration-300 text-gray-900 dark:text-white',
                        isOpen ? 'rotate-180' : 'rotate-0'
                    ]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="isOpen"
                class="absolute top-[45px] right-0 bg-indigo-soft-200/90 dark:bg-gray-800/90 rounded-lg border !border-indigo-soft-400 dark:!border-gray-700 overflow-hidden min-w-[180px]"
            >
                <div>
                    <!-- Subscriptions Link -->
                    <button
                        class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative text-gray-900 dark:text-white"
                        @click="navigateTo('/subscriptions')"
                    >
                        <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                            <!-- Calendar icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $t('auth.subscriptions') }}
                        </span>
                    </button>

                    <!-- Profile Link -->
                    <button
                        class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative text-gray-900 dark:text-white"
                        @click="navigateTo('/profile')"
                    >
                        <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                            <!-- User icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $t('auth.profile') }}
                        </span>
                    </button>

                    <!-- Logout -->
                    <button
                        class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative text-gray-900 dark:text-white"
                        @click="handleLogout"
                    >
                        <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                            <!-- Logout icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            {{ $t('services.logout') }}
                        </span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useServicesStore } from '../stores/services';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const servicesStore = useServicesStore();
const router = useRouter();
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const navigateTo = (path: string) => {
    isOpen.value = false;
    router.push(path);
};

const handleLogout = async () => {
    console.log('[UserMenu] Logging out...');
    isOpen.value = false;
    
    // Останавливаем все сервисы перед выходом
    await servicesStore.stopAllServices();
    
    // Выходим из системы
    await authStore.logout();
    
    // Редирект на страницу логина
    router.push('/login');
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<style scoped>
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

.d-flex {
    display: flex;
}

.gap-5 {
    gap: 1.25rem;
}

.px-lg-3 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}
</style>

