/**
 * Утилиты для предзагрузки ресурсов
 */

/**
 * Prefetch для предсказуемых маршрутов
 */
export function prefetchRoute(routePath: string) {
    // Создаем link элемент для prefetch
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = routePath;
    link.as = 'document';
    document.head.appendChild(link);
}

/**
 * Prefetch для критических API данных
 */
export function prefetchAPI(endpoint: string) {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = `/api${endpoint}`;
    link.as = 'fetch';
    link.crossOrigin = 'anonymous';
    document.head.appendChild(link);
}

/**
 * Prefetch для изображений
 */
export function prefetchImage(src: string) {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = src;
    link.as = 'image';
    document.head.appendChild(link);
}

/**
 * Prefetch критических маршрутов после загрузки страницы
 */
export function prefetchCriticalRoutes() {
    // Prefetch популярные маршруты
    const criticalRoutes = [
        '/articles',
        '/login',
        '/register'
    ];

    // Задержка для неблокирующей загрузки
    setTimeout(() => {
        criticalRoutes.forEach(route => {
            prefetchRoute(route);
        });
    }, 2000);
}

