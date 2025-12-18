<template>
    <div class="flex flex-col space-y-4 mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="separator">{{ $t('auth.orContinueWith') }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <!-- Google кнопка -->
            <a
                class="flex items-center dark:text-white dark:hover:bg-gray-300 dark:hover:text-gray-800 justify-center border border-gray-300 rounded-lg px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                @click="openGoogleAuth"
            >
                <img class="w-5 h-5 mr-2" src="/img/google.png" alt="google" width="20" height="20" style="aspect-ratio: 1 / 1;" />
                <span>{{ $t('auth.google') }}</span>
            </a>

            <!-- Telegram кнопка -->
            <button
                class="flex items-center justify-center border-[#1a94d2] rounded-lg px-4 py-3 bg-[#1a94d2] hover:bg-[#1a94d2]/80 transition-colors"
                @click="initTelegramAuth"
            >
                <img class="w-5 h-5 mr-2" src="/img/telegram.png" alt="telegram" width="20" height="20" style="aspect-ratio: 1 / 1;" />
                <span class="text-white">{{ $t('auth.telegram') }}</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import '../../types/telegram.d.ts';

const authStore = useAuthStore();

const router = useRouter();
const route = useRoute();

const openGoogleAuth = () => {
    // Используем специальный роут для принудительного выбора аккаунта
    const url = '/auth/google/reauth';
    const features = `
    toolbar=no,
    location=no,
    status=no,
    menubar=no,
    scrollbars=yes,
    resizable=yes,
    width=800,
    height=600
  `;

    const popup = window.open(url, 'googleAuth', features.replace(/\s+/g, ''));

    // Слушаем сообщения от popup окна
    const messageListener = async (event: MessageEvent) => {
        // Проверяем, что сообщение от нашего домена
        if (event.origin !== window.location.origin) {
            return;
        }

        if (event.data.type === 'GOOGLE_AUTH_SUCCESS') {
            // Успешная авторизация
            const { token, user } = event.data.data;

            // Устанавливаем новый токен и пользователя
            authStore.setToken(token);
            authStore.setUser(user);

            // Убираем слушатель
            window.removeEventListener('message', messageListener);

            // Принудительно обновляем страницу для полного обновления состояния
            const redirectTo = route.query.redirect as string;
            router.push(redirectTo || '/');
        } else if (event.data.type === 'GOOGLE_AUTH_ERROR') {
            // Ошибка авторизации
            console.error('Google auth error:', event.data.data.error);

            // Убираем слушатель
            window.removeEventListener('message', messageListener);
        }
    };

    // Добавляем слушатель сообщений
    window.addEventListener('message', messageListener);

    // Убираем слушатель если popup закрыт без авторизации
    const checkClosed = setInterval(() => {
        if (popup?.closed) {
            window.removeEventListener('message', messageListener);
            clearInterval(checkClosed);
        }
    }, 1000);
};

// Метод для инициализации Telegram Login Widget
const initTelegramAuth = () => {
    // Проверяем, загружен ли Telegram Widget скрипт
    if (!window.Telegram) {
        loadTelegramScript();
    } else {
        showTelegramPopup();
    }
};

// Загрузка Telegram Widget скрипта
const loadTelegramScript = () => {
    if (document.getElementById('telegram-login-script')) return;

    const script = document.createElement('script');
    script.id = 'telegram-login-script';
    script.src = 'https://telegram.org/js/telegram-widget.js';
    script.async = true;
    script.onload = showTelegramPopup;
    document.head.appendChild(script);
};

// Показать всплывающее окно Telegram авторизации
const showTelegramPopup = () => {
    // Для этого вам нужно создать Telegram бота и получить его ID
    // Здесь мы временно жёстко указываем ID бота, чтобы исправить ошибку Bot id required
    // TODO: позже можно вернуть использование переменной окружения VITE_APP_TELEGRAM_BOT_ID
    const botId = '8267596067';

    if (window.Telegram && window.Telegram.Login) {
        console.log('Telegram auth: calling Login.auth with bot_id', botId);
        window.Telegram.Login.auth({ bot_id: botId }, data => {
            console.log('Telegram auth: callback received', { hasData: !!data, dataKeys: data ? Object.keys(data) : [] });
            if (data) {
                handleTelegramAuth(data);
            } else {
                console.warn('Telegram auth: no data received from Telegram');
            }
        });
    } else {
        console.error('Telegram auth: Telegram.Login not available');
    }
};

// Обработка данных авторизации Telegram
const handleTelegramAuth = async (data: any) => {
    try {
        console.log('Telegram auth: sending data to callback', { hasId: !!data?.id, hasHash: !!data?.hash });
        
        const response = await fetch('/auth/telegram/callback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json'
            },
            body: JSON.stringify(data)
        });

        console.log('Telegram auth: response status', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Telegram auth: server error', {
                status: response.status,
                statusText: response.statusText,
                body: errorText
            });
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Telegram auth: response data', { hasToken: !!result.token, hasUser: !!result.user, success: result.success });

        if (result.token) {
            authStore.setToken(result.token);
            authStore.setUser(result.user);

            const redirectTo = route.query.redirect as string;
            router.push(redirectTo || '/');
        } else {
            console.error('Telegram auth: no token in response', result);
        }
    } catch (error) {
        console.error('Telegram auth error:', error);
    }
};
</script>

<style scoped>
.separator {
    @apply bg-white dark:bg-gray-800 dark:text-gray-300 px-4 text-gray-500;
}
</style>
