/**
 * Проверяет поддержку WebP в браузере
 */
export function supportsWebP(): Promise<boolean> {
    return new Promise((resolve) => {
        const webP = new Image();
        webP.onload = webP.onerror = () => {
            resolve(webP.height === 2);
        };
        webP.src = 'data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
    });
}

/**
 * Получает WebP версию изображения, если она доступна и браузер поддерживает WebP
 * @param originalUrl Оригинальный URL изображения
 * @param webPSupported Поддержка WebP браузером (можно передать результат supportsWebP())
 * @returns URL WebP версии или оригинальный URL
 */
export function getWebPUrl(originalUrl: string, webPSupported: boolean = false): string {
    if (!webPSupported) {
        return originalUrl;
    }

    // Заменяем расширение на .webp
    const webpUrl = originalUrl.replace(/\.(jpg|jpeg|png|gif)$/i, '.webp');
    
    // Если URL изменился, значит это не WebP изначально
    if (webpUrl !== originalUrl) {
        return webpUrl;
    }

    return originalUrl;
}

/**
 * Создает picture элемент с поддержкой WebP и fallback
 * @param originalUrl Оригинальный URL изображения
 * @param alt Alt текст
 * @param className CSS классы
 * @param width Ширина
 * @param height Высота
 * @returns Объект с srcSet для picture элемента
 */
export function createPictureSources(
    originalUrl: string,
    webPSupported: boolean = false
): { webp?: string; fallback: string } {
    const result: { webp?: string; fallback: string } = {
        fallback: originalUrl
    };

    if (webPSupported) {
        const webpUrl = originalUrl.replace(/\.(jpg|jpeg|png|gif)$/i, '.webp');
        if (webpUrl !== originalUrl) {
            result.webp = webpUrl;
        }
    }

    return result;
}

