import { ref, onMounted } from 'vue';
import { supportsWebP, getWebPUrl } from '@/utils/imageUtils';

const webPSupported = ref<boolean | null>(null);

/**
 * Composable для работы с WebP изображениями
 * Проверяет поддержку WebP один раз при загрузке приложения
 */
export function useWebP() {
    // Проверяем поддержку WebP при первом использовании
    if (webPSupported.value === null) {
        supportsWebP().then(supported => {
            webPSupported.value = supported;
        });
    }

    /**
     * Получает WebP версию URL, если браузер поддерживает WebP
     */
    const getOptimizedUrl = (originalUrl: string): string => {
        if (webPSupported.value === true) {
            return getWebPUrl(originalUrl, true);
        }
        return originalUrl;
    };

    return {
        webPSupported,
        getOptimizedUrl
    };
}

