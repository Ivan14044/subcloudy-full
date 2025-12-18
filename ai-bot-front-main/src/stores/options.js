import { defineStore } from 'pinia';
import axios from 'axios';
import { apiCache, createCacheKey } from '@/utils/cacheUtils';

export const useOptionStore = defineStore('options', {
    state: () => ({
        options: [],
        isLoaded: false
    }),
    actions: {
        async fetchData() {
            if (this.isLoaded) return;

            const cacheKey = createCacheKey('/options');
            const cached = apiCache.get(cacheKey);
            if (cached) {
                this.options = cached;
                this.isLoaded = true;
                return;
            }

            try {
                const response = await axios.get('/options');
                this.options = response.data;
                this.isLoaded = true;
                // Кешируем на 10 минут
                apiCache.set(cacheKey, response.data, 10 * 60 * 1000);
            } catch (error) {
                console.error('Error fetching options:', error);
            }
        }
    }
});
