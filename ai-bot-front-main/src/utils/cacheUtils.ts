/**
 * Утилиты для кеширования API ответов
 */

interface CacheEntry<T> {
    data: T;
    timestamp: number;
    ttl: number; // Time to live в миллисекундах
}

class APICache {
    private cache: Map<string, CacheEntry<any>> = new Map();
    private defaultTTL = 5 * 60 * 1000; // 5 минут по умолчанию

    /**
     * Получить данные из кеша
     */
    get<T>(key: string): T | null {
        const entry = this.cache.get(key);
        if (!entry) return null;

        const now = Date.now();
        if (now - entry.timestamp > entry.ttl) {
            // Кеш истек
            this.cache.delete(key);
            return null;
        }

        return entry.data as T;
    }

    /**
     * Сохранить данные в кеш
     */
    set<T>(key: string, data: T, ttl?: number): void {
        this.cache.set(key, {
            data,
            timestamp: Date.now(),
            ttl: ttl || this.defaultTTL
        });
    }

    /**
     * Очистить кеш
     */
    clear(): void {
        this.cache.clear();
    }

    /**
     * Удалить конкретный ключ из кеша
     */
    delete(key: string): void {
        this.cache.delete(key);
    }

    /**
     * Очистить устаревшие записи
     */
    cleanup(): void {
        const now = Date.now();
        for (const [key, entry] of this.cache.entries()) {
            if (now - entry.timestamp > entry.ttl) {
                this.cache.delete(key);
            }
        }
    }
}

// Глобальный экземпляр кеша
export const apiCache = new APICache();

// Периодическая очистка устаревших записей (каждые 10 минут)
if (typeof window !== 'undefined') {
    setInterval(() => {
        apiCache.cleanup();
    }, 10 * 60 * 1000);
}

/**
 * Создать ключ кеша для API запроса
 */
export function createCacheKey(endpoint: string, params?: Record<string, any>): string {
    const paramsStr = params ? JSON.stringify(params) : '';
    return `${endpoint}${paramsStr}`;
}

