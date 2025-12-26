import { defineStore } from 'pinia';
import axios from 'axios';
import { apiCache, createCacheKey } from '@/utils/cacheUtils';

interface Page {
    id: number;
    title: string;
    content: string;
    slug: string;
    [key: string]: any;
}

interface PageState {
    pages: Record<string, Page>;
    page: Page | null;
    isLoaded: boolean;
    currentLocale: string;
}

export const usePageStore = defineStore('pages', {
    state: (): PageState => ({
        pages: {},
        page: null,
        isLoaded: false,
        currentLocale: 'ru'
    }),
    actions: {
        async fetchData(locale = 'ru', force = false) {
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
                
                if (response && response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
                    this.pages = response.data;
                    this.currentLocale = locale;
                    this.isLoaded = true;
                    apiCache.set(cacheKey, response.data, 10 * 60 * 1000);
                } else {
                    this.pages = {};
                    this.isLoaded = false;
                }
            } catch (error) {
                console.error('[PageStore] Error fetching pages:', error);
                this.pages = {};
                this.isLoaded = false;
            }
        },
        setPage(payload: Page | null) {
            this.page = payload;
        }
    }
});


