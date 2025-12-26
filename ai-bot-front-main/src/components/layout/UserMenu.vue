<template>
    <div v-if="isAuthenticated" id="userMenu" ref="dropdownRef" class="d-flex gap-5 relative">
        <button
            ref="buttonRef"
            class="px-2 px-lg-3 d-flex py-2 h-[32px] text-base leading-4 items-center rounded-lg hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 group"
            @click="toggleDropdown"
            :aria-label="$t('auth.userMenu')"
            :aria-expanded="isOpen"
        >
            <User class="w-5 h-5 flex-shrink-0" />

            <span class="flex items-center sm:pl-2 min-w-0">
                <span
                    v-if="authStore.user?.name"
                    class="hidden md:inline-block truncate whitespace-nowrap overflow-hidden font-normal text-[15px]"
                    :class="[
                        'max-w-[90px]', // < md
                        'xl:max-w-[150px]', // ≥ xl
                        '2xl:max-w-[220px]' // ≥ 2xl
                    ]"
                    :title="authStore.user.name"
                >
                    {{ authStore.user.name }}
                </span>

                <ChevronDown
                    :class="[
                        'xl:ml-1 w-4 h-4 flex-shrink-0 transition-transform duration-300',
                        isOpen ? 'rotate-180' : 'rotate-0'
                    ]"
                />
            </span>
        </button>

        <!-- Dropdown Menu -->
        <Teleport to="body">
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
                    ref="menuRef"
                    :style="dropdownStyle"
                    class="user-menu-dropdown fixed min-w-[160px] z-[9999]"
                >
                    <div class="liquid-glass-effect"></div>
                    <div class="liquid-glass-tint"></div>
                    <div class="liquid-glass-shine"></div>
                    <div class="liquid-glass-text">
                        <button
                            class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative"
                            @click="navigateTo('/subscriptions')"
                        >
                            <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                                <CalendarCheck class="w-5" />
                                {{ $t('auth.subscriptions') }}
                            </span>
                        </button>
                        <button
                            class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative"
                            @click="navigateTo('/profile')"
                        >
                            <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                                <UserPen class="w-5" />
                                {{ $t('auth.profile') }}
                            </span>
                        </button>
                        <button
                            class="flex items-center gap-3 w-full px-4 py-3 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative"
                            @click="handleAuthAction"
                        >
                            <span class="relative z-10 flex whitespace-nowrap gap-2 items-center">
                                <LogOut class="w-5" />
                                {{ $t('auth.logoutLink') }}
                            </span>
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
    <div v-else id="loginMenu" ref="dropdownRef" class="d-flex align-center top-3 right-6 z-50">
        <button
            class="px-2 px-lg-3 d-flex py-2 h-[32px] text-base leading-4 align-center backdrop-blur-sm rounded-lg hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 group"
            @click="handleAuthAction"
        >
            <LogIn class="w-5 h-5" />
            <span class="pl-2">
                {{ $t('auth.loginLink') }}
            </span>
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, nextTick, watch, Teleport } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { LogIn, User, ChevronDown, LogOut, UserPen, CalendarCheck } from 'lucide-vue-next';
import { useCartStore } from '@/stores/cart';

// SAVED BY AI
const cartStore = useCartStore();
const authStore = useAuthStore();
const router = useRouter();
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const buttonRef = ref<HTMLElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);
const dropdownStyle = ref({ top: '0px', right: '0px' });
const isAuthenticated = computed(() => !!authStore.user);

const updateDropdownPosition = () => {
    if (!buttonRef.value || !isOpen.value) return;
    
    nextTick(() => {
        const rect = buttonRef.value!.getBoundingClientRect();
        dropdownStyle.value = {
            top: `${rect.bottom + 5}px`,
            right: `${window.innerWidth - rect.right}px`
        };
    });
};

const handleAuthAction = () => {
    if (isAuthenticated.value) {
        authStore.logout();
        isOpen.value = false;
        cartStore.clearCart();
        router.push('/');
    } else {
        router.push('/login');
    }
};

const navigateTo = async (path: string) => {
    isOpen.value = false;
    await router.push(path);
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        updateDropdownPosition();
    }
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as Node;
    
    // ИГНОРИРУЕМ клики внутри модального окна поддержки
    const supportModal = document.querySelector('.support-modal-container');
    if (supportModal && supportModal.contains(target)) {
        return;
    }
    
    if (
        dropdownRef.value && 
        !dropdownRef.value.contains(target) &&
        menuRef.value &&
        !menuRef.value.contains(target)
    ) {
        isOpen.value = false;
    }
};

watch(isOpen, (newVal) => {
    if (newVal) {
        updateDropdownPosition();
        window.addEventListener('scroll', updateDropdownPosition);
        window.addEventListener('resize', updateDropdownPosition);
    } else {
        window.removeEventListener('scroll', updateDropdownPosition);
        window.removeEventListener('resize', updateDropdownPosition);
    }
});

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    window.removeEventListener('scroll', updateDropdownPosition);
    window.removeEventListener('resize', updateDropdownPosition);
});
</script>

<style scoped>
/* Liquid Glass Effect для dropdown меню пользователя */
.user-menu-dropdown {
    position: fixed;
    box-shadow: 0 6px 6px rgba(0, 0, 0, 0.2), 0 0 20px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem; /* rounded-lg */
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.user-menu-dropdown .liquid-glass-effect {
    position: absolute;
    z-index: 0;
    inset: 0;
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    filter: url(#header-glass-distortion);
    overflow: hidden;
    isolation: isolate;
    border-radius: 0.5rem;
}

.user-menu-dropdown .liquid-glass-tint {
    z-index: 1;
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 0.5rem;
}

.dark .user-menu-dropdown .liquid-glass-tint {
    background: rgba(31, 41, 55, 0.4);
}

.user-menu-dropdown .liquid-glass-shine {
    position: absolute;
    inset: 0;
    z-index: 2;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 
        inset 2px 2px 1px 0 rgba(255, 255, 255, 0.5),
        inset -1px -1px 1px 1px rgba(255, 255, 255, 0.5);
    pointer-events: none;
}

.dark .user-menu-dropdown .liquid-glass-shine {
    box-shadow: 
        inset 2px 2px 1px 0 rgba(255, 255, 255, 0.1),
        inset -1px -1px 1px 1px rgba(255, 255, 255, 0.1);
}

.user-menu-dropdown .liquid-glass-text {
    z-index: 3;
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.user-menu-dropdown .liquid-glass-text button {
    color: #1f2937; /* text-gray-900 */
}

.dark .user-menu-dropdown .liquid-glass-text button {
    color: #f9fafb; /* dark:text-white */
}
</style>
