<template>
    <div
        class="min-h-screen py-3 w-full bg-[#fafafa] dark:bg-gray-900 flex items-center justify-center"
    >
        <div
            class="max-w-sm w-full lg:w-1/2 flex items-center justify-center p-8 bg-white dark:!bg-gray-800 rounded-[12px] shadow-lg relative"
        >
            <div class="w-full">
                <!-- Language Selector (top-right corner like website) -->
                <div class="flex items-center justify-end absolute top-3 right-3">
                    <LanguageSelector />
                </div>

                <!-- Логотип -->
                <div class="flex items-center justify-center mb-6 -mt-2 w-full">
                    <div class="flex items-center flex-col cursor-pointer" @click="openWebsite">
                        <img
                            :src="logo"
                            alt="SubCloudy"
                            class="w-16 h-16 object-contain spin-slow-reverse mb-2"
                        />
                        <span
                            class="text-black dark:!text-white mt-1 text-sm font-semibold leading-none"
                        >
                            SubCloudy
                        </span>
                    </div>
                </div>

                <!-- Заголовок -->
                <h1 class="text-2xl text-gray-900 dark:text-white mb-2 text-center font-medium">
                    {{ $t('auth.loginTitle') }}
                </h1>
                <p class="text-gray-500 dark:text-gray-300 mb-6 text-center">
                    {{ $t('auth.loginSubtitle') }}
                </p>

                <!-- Форма -->
                <form class="space-y-4" @submit.prevent="handleSubmit">
                    <!-- Email -->
                    <div class="space-y-2">
                        <div class="relative">
                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                class="input-field dark:!border-gray-500 dark:text-gray-300"
                                :placeholder="$t('auth.email')"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <p v-if="errors.email" class="text-red-500 text-sm">
                            {{ errors.email[0] }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="relative">
                            <input
                                id="password"
                                v-model="password"
                                type="password"
                                class="input-field dark:!border-gray-500 dark:text-gray-300"
                                :placeholder="$t('auth.password')"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <p v-if="errors.password" class="text-red-500 text-sm">
                            {{ errors.password[0] }}
                        </p>
                    </div>

                    <!-- Remember me + Forgot Password -->
                    <div class="flex flex-row items-center gap-2 justify-between">
                        <div class="relative">
                            <input id="remember" v-model="remember" type="checkbox" class="mr-2" />
                            <label for="remember" class="text-sm dark:text-gray-300">
                                {{ $t('auth.rememberMe') }}
                            </label>
                        </div>
                        <div class="relative">
                            <a
                                href="https://subcloudy.com/forgot-password"
                                target="_blank"
                                class="text-sm text-[#0065FF] dark:text-blue-600 hover:!text-blue-500"
                            >
                                {{ $t('auth.forgotPassword') }}
                            </a>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div>
                        <button type="submit" :disabled="loading" class="auth-button primary">
                            <span v-if="!loading">{{ $t('auth.loginButton') }}</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ $t('auth.signingIn') }}
                            </span>
                        </button>
                    </div>

                    <!-- Ссылка на регистрацию -->
                    <p class="text-center text-sm text-gray-600 dark:text-gray-300 mt-5">
                        {{ $t('auth.noAccount') }}
                        <a
                            href="https://subcloudy.com/register"
                            target="_blank"
                            class="inline-flex items-center justify-center ml-2 px-2.5 py-1 text-xs border rounded-md bg-white text-gray-900 hover:!text-white hover:!bg-blue-600 transition font-medium dark:bg-gray-800 dark:text-gray-100 dark:hover:!bg-blue-700 dark:hover:!text-white"
                        >
                            {{ $t('auth.register') }}
                        </a>
                    </p>
                </form>

                <!-- Social Auth Buttons -->
                <SocialAuthButtons />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import SocialAuthButtons from '../components/SocialAuthButtons.vue';
import LanguageSelector from '../components/LanguageSelector.vue';
import logo from '../assets/logo.webp';

const email = ref('');
const password = ref('');
const remember = ref(false);
const loading = ref(false);
const errors = ref<{ email?: string[]; password?: string[] }>({});
const router = useRouter();
const authStore = useAuthStore();

const openWebsite = () => {
    window.open('https://subcloudy.com', '_blank');
};

const handleSubmit = async () => {
    console.log('[LoginPage] Submitting login...');
    loading.value = true;
    errors.value = {};
    
    try {
        const result = await authStore.login(email.value, password.value);
        console.log('[LoginPage] Login result:', result);
        
        loading.value = false;
        
        if (result.success) {
            console.log('[LoginPage] Login successful, navigating to services...');
            await router.push({ name: 'Services' });
            console.log('[LoginPage] Navigation complete');
        } else {
            console.error('[LoginPage] Login failed:', result.error);
            // Устанавливаем ошибки для отображения
            if (result.error) {
                errors.value = { email: [result.error] };
            }
        }
    } catch (err) {
        console.error('[LoginPage] Login error:', err);
        loading.value = false;
    }
};
</script>

<style scoped>
.input-field {
    @apply w-full px-4 py-3 border border-solid border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all;
}

.auth-button {
    @apply w-full text-gray-900 hover:text-white bg-white hover:!bg-blue-600 border font-medium py-[10px] rounded-lg transition-all flex items-center justify-center gap-2;
}

.auth-button:disabled {
    @apply opacity-50 cursor-not-allowed text-[#0065FF]/80 border-[#0065FF]/80;
}

.auth-button.primary {
    @apply bg-blue-500 dark:bg-blue-900 text-white hover:bg-blue-600 text-white;
}

.auth-button.primary:disabled {
    @apply opacity-50 cursor-not-allowed bg-[#0065FF]/80;
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
</style>
