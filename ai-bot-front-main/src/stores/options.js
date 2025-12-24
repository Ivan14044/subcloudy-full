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
                // Валидируем и очищаем данные перед сохранением
                const data = response.data?.data || response.data;
                
                // Если данные - массив, проверяем каждый элемент
                if (Array.isArray(data)) {
                    this.options = data.map(option => {
                        if (!option || !option.key) return option;
                        
                        // Проверяем, что footer_menu и header_menu - валидный JSON
                        if (option.key === 'footer_menu' || option.key === 'header_menu') {
                            try {
                                // Если значение - строка, пытаемся распарсить
                                if (typeof option.value === 'string' && option.value.trim()) {
                                    const parsed = JSON.parse(option.value);
                                    // Если парсинг успешен, оставляем как есть
                                    option.value = option.value;
                                } else if (typeof option.value === 'object') {
                                    // Если уже объект, валидируем через JSON
                                    JSON.stringify(option.value);
                                }
                            } catch (e) {
                                console.warn(`[OptionStore] Invalid JSON in ${option.key}, error:`, e.message);
                                console.warn(`[OptionStore] Invalid value (first 100 chars):`, String(option.value).substring(0, 100));
                                // Устанавливаем пустой объект вместо некорректного JSON
                                option.value = '{}';
                            }
                        }
                        return option;
                    });
                } else if (typeof data === 'object' && data !== null) {
                    // Если данные - объект, проверяем footer_menu и header_menu
                    const cleanedData = { ...data };
                    // Безопасный forEach - используем массив ключей
                    const menuKeys = ['footer_menu', 'header_menu'];
                    if (Array.isArray(menuKeys)) {
                        menuKeys.forEach(key => {
                            if (cleanedData[key]) {
                                try {
                                    if (typeof cleanedData[key] === 'string' && cleanedData[key].trim()) {
                                        JSON.parse(cleanedData[key]);
                                    } else if (typeof cleanedData[key] === 'object') {
                                        JSON.stringify(cleanedData[key]);
                                    }
                                } catch (e) {
                                    console.warn(`[OptionStore] Invalid JSON in ${key}, error:`, e.message);
                                    console.warn(`[OptionStore] Invalid value (first 100 chars):`, String(cleanedData[key]).substring(0, 100));
                                    cleanedData[key] = '{}';
                                }
                            }
                        });
                    }
                    this.options = cleanedData;
                } else {
                    this.options = data;
                }
                
                this.isLoaded = true;
                // Кешируем на 10 минут
                apiCache.set(cacheKey, this.options, 10 * 60 * 1000);
            } catch (error) {
                console.error('[OptionStore] Error fetching options:', error);
                // Устанавливаем пустые данные, чтобы не блокировать приложение
                this.options = [];
                this.isLoaded = false;
            }
        }
    }
});
