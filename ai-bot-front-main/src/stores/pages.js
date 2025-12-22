import { defineStore } from 'pinia';
import axios from 'axios';
import { apiCache, createCacheKey } from '@/utils/cacheUtils';

export const usePageStore = defineStore('pages', {
    state: () => ({
        pages: {},
        page: null,
        isLoaded: false,
        currentLocale: 'ru'
    }),
    actions: {
        async fetchData(locale = 'ru', force = false) {
            // Если локаль не изменилась и данные уже загружены, не перезагружаем (если не force)
            if (!force && this.isLoaded && this.currentLocale === locale) {
                return;
            }

            const cacheKey = createCacheKey('/pages', { lang: locale });
            const cached = apiCache.get(cacheKey);
            if (cached) {
                this.pages = cached;
                this.currentLocale = locale;
                this.isLoaded = true;
                return;
            }

            try {
                const response = await axios.get('/pages', {
                    params: { lang: locale }
                });
                
                // Убеждаемся, что response.data - это объект
                if (response && response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
                    this.pages = response.data;
                    this.currentLocale = locale;
                    this.isLoaded = true;
                    // Кешируем на 10 минут
                    apiCache.set(cacheKey, response.data, 10 * 60 * 1000);
                } else {
                    this.pages = {};
                    this.isLoaded = false;
                }
            } catch (error) {
                console.error('[PageStore] Error fetching pages:', error);
                // В случае ошибки устанавливаем пустой объект, чтобы не блокировать навигацию
                this.pages = {};
                this.isLoaded = false;
            }
        },
        setPage(payload) {
            this.page = payload;
        }
    }
});
