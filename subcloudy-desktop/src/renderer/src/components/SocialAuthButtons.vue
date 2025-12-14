<template>
    <div class="flex flex-col space-y-4 mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="separator">{{ $t('auth.loginWith') }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <!-- Google кнопка -->
            <button
                class="flex items-center dark:text-white dark:hover:bg-gray-300 dark:hover:text-gray-800 justify-center border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer"
                @click="openGoogleAuth"
                :disabled="loading"
                type="button"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Google</span>
            </button>

            <!-- Telegram кнопка -->
            <button
                class="flex items-center justify-center border-[#1a94d2] rounded-lg px-4 py-3 bg-[#1a94d2] hover:bg-[#1a94d2]/80 transition-colors"
                @click="openTelegramAuth"
                :disabled="loading"
                type="button"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="white">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                </svg>
                <span class="text-white">Telegram</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);

const openGoogleAuth = async () => {
    if (loading.value) return;
    
    loading.value = true;
    
    try {
        console.log('[SocialAuth] Starting Google OAuth...');
        
        const result = await window.electronAPI.auth.loginWithGoogle();
        
        if (result.success && result.token) {
            console.log('[SocialAuth] Google auth successful!');
            
            // Обновляем auth store
            await authStore.checkAuth();
            
            // Редирект на главную страницу
            router.push('/services');
        } else {
            console.error('[SocialAuth] Google auth failed:', result.error);
            alert('Ошибка авторизации через Google: ' + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error: any) {
        console.error('[SocialAuth] Google auth error:', error);
        alert('Ошибка авторизации через Google');
    } finally {
        loading.value = false;
    }
};

const openTelegramAuth = async () => {
    if (loading.value) return;
    
    loading.value = true;
    
    try {
        console.log('[SocialAuth] Starting Telegram OAuth...');
        
        const result = await window.electronAPI.auth.loginWithTelegram();
        
        if (result.success && result.token) {
            console.log('[SocialAuth] Telegram auth successful!');
            
            // Обновляем auth store
            await authStore.checkAuth();
            
            // Редирект на главную страницу
            router.push('/services');
        } else {
            console.error('[SocialAuth] Telegram auth failed:', result.error);
            alert('Ошибка авторизации через Telegram: ' + (result.error || 'Неизвестная ошибка'));
        }
    } catch (error: any) {
        console.error('[SocialAuth] Telegram auth error:', error);
        alert('Ошибка авторизации через Telegram');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.separator {
    @apply bg-white dark:bg-gray-800 dark:text-gray-300 px-4 text-gray-500;
}
</style>

