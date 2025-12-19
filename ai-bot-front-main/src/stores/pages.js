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
            console.log('[PageStore] fetchData called with locale:', locale);
            console.log('[PageStore] Current state - isLoaded:', this.isLoaded, 'currentLocale:', this.currentLocale);
            
            // ???????? ???????????? ???? ???????????????????? ?? ???????????? ?????? ??????????????????, ???? ??????????????????????????
            if (this.isLoaded && this.currentLocale === locale) {
                console.log('[PageStore] Data already loaded for locale, skipping fetch');
                return;
            }

            const cacheKey = createCacheKey('/pages', { lang: locale });
            const cached = apiCache.get(cacheKey);
            if (cached) {
                console.log('[PageStore] Using cached data');
                this.pages = cached;
                this.currentLocale = locale;
                this.isLoaded = true;
                console.log('[PageStore] Cached pages slugs:', Object.keys(this.pages));
                return;
            }

            try {
                console.log('[PageStore] Fetching pages from API...');
                const response = await axios.get('/pages', {
                    params: { lang: locale }
                });
                console.log('[PageStore] API response received:', response);
                console.log('[PageStore] Response data type:', typeof response.data);
                console.log('[PageStore] Response data:', response.data);
                
                // ????????????????????, ?????? response.data - ?????? ????????????
                if (response.data && typeof response.data === 'object') {
                    this.pages = response.data;
                    this.currentLocale = locale;
                    this.isLoaded = true;
                    console.log('[PageStore] Pages loaded successfully, slugs:', Object.keys(this.pages));
                    // ???????????????? ???? 10 ??????????
                    apiCache.set(cacheKey, response.data, 10 * 60 * 1000);
                } else {
                    console.warn('[PageStore] Invalid pages data received:', response.data);
                    this.pages = {};
                }
            } catch (error) {
                console.error('[PageStore] Error fetching pages:', error);
                console.error('[PageStore] Error response:', error.response);
                console.error('[PageStore] Error message:', error.message);
                // ?? ???????????? ???????????? ?????????????????????????? ???????????? ????????????, ?????????? ???? ?????????????????????? ??????????????????
                this.pages = {};
                this.isLoaded = false;
            }
        },
        setPage(payload) {
            console.log('[PageStore] setPage called with payload:', payload);
            this.page = payload;
            console.log('[PageStore] Page set, current page:', this.page);
        }
    }
});
