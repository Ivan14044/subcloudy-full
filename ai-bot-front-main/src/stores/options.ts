import { defineStore } from 'pinia';
import axios from 'axios';
import { apiCache, createCacheKey } from '@/utils/cacheUtils';

interface Option {
    key: string;
    value: any;
    [key: string]: any;
}

interface OptionState {
    options: Option[] | Record<string, any>;
    isLoaded: boolean;
}

export const useOptionStore = defineStore('options', {
    state: (): OptionState => ({
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
                const data = response.data?.data || response.data;
                
                if (Array.isArray(data)) {
                    this.options = data.map((option: Option) => {
                        if (!option || !option.key) return option;
                        
                        if (option.key === 'footer_menu' || option.key === 'header_menu') {
                            try {
                                if (typeof option.value === 'string' && option.value.trim()) {
                                    JSON.parse(option.value);
                                } else if (typeof option.value === 'object') {
                                    JSON.stringify(option.value);
                                }
                            } catch (e: any) {
                                console.warn(`[OptionStore] Invalid JSON in ${option.key}, error:`, e.message);
                                option.value = '{}';
                            }
                        }
                        return option;
                    });
                } else if (typeof data === 'object' && data !== null) {
                    const cleanedData = { ...data };
                    const menuKeys = ['footer_menu', 'header_menu'];
                    menuKeys.forEach(key => {
                        if (cleanedData[key]) {
                            try {
                                if (typeof cleanedData[key] === 'string' && cleanedData[key].trim()) {
                                    JSON.parse(cleanedData[key]);
                                } else if (typeof cleanedData[key] === 'object') {
                                    JSON.stringify(cleanedData[key]);
                                }
                            } catch (e: any) {
                                console.warn(`[OptionStore] Invalid JSON in ${key}, error:`, e.message);
                                cleanedData[key] = '{}';
                            }
                        }
                    });
                    this.options = cleanedData;
                } else {
                    this.options = data;
                }
                
                this.isLoaded = true;
                apiCache.set(cacheKey, this.options, 10 * 60 * 1000);
            } catch (error) {
                console.error('[OptionStore] Error fetching options:', error);
                this.options = [];
                this.isLoaded = false;
            }
        }
    }
});

