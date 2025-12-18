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
        async fetchData(locale = 'ru') {
            // Если локаль не изменилась и данные уже загружены, не перезагружаем
            if (this.isLoaded && this.currentLocale === locale) return;

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
                this.pages = response.data;
                this.currentLocale = locale;
                this.isLoaded = true;
                // Кешируем на 10 минут
                apiCache.set(cacheKey, response.data, 10 * 60 * 1000);
            } catch (error) {
                console.error('Error fetching pages:', error);
            }
        },
        setPage(payload) {
            this.page = payload;
        }
    }
});
