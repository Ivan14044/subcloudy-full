import { defineStore } from 'pinia';
import axios from 'axios';

export interface Services {
    id: number;
    name: string;
    code: string;
    logo: string;
    [key: string]: unknown;
}

interface ServiceState {
    services: Services[];
    isLoaded: boolean;
}

export const useServiceStore = defineStore('services', {
    state: (): ServiceState => ({
        services: [],
        isLoaded: false
    }),

    actions: {
        async fetchData() {
            if (this.isLoaded) return;

            try {
                const response = await axios.get<Services[]>('/services');
                
                // Определяем базовый URL для статических файлов (логотипы, изображения)
                // Используем origin текущей страницы, так как статические файлы обслуживаются тем же доменом
                const baseUrl = window.location.origin;

                this.services = response.data.map(service => {
                    let logoUrl = service.logo;
                    
                    // Если уже полный URL (http/https), оставляем как есть
                    if (logoUrl.startsWith('http://') || logoUrl.startsWith('https://')) {
                        return { ...service, logo: logoUrl };
                    }
                    
                    // Если путь начинается с /, добавляем только origin
                    if (logoUrl.startsWith('/')) {
                        logoUrl = `${baseUrl}${logoUrl}`;
                    } else {
                        // Иначе добавляем origin и слэш перед путем
                        logoUrl = `${baseUrl}/${logoUrl}`;
                    }
                    
                    return { ...service, logo: logoUrl };
                });

                this.isLoaded = true;
            } catch (error) {
                console.error('Error fetching services:', error);
            }
        },

        getById(id: number): Services | undefined {
            // Ищем по строгому сравнению и по приведению типов
            return this.services.find(service => service.id === id || Number(service.id) === Number(id));
        }
    }
});
