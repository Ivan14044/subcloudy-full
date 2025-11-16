import { defineStore } from 'pinia';
import axios from 'axios';

export const useOptionStore = defineStore('options', {
    state: () => ({
        options: [],
        isLoaded: false
    }),
    actions: {
        async fetchData() {
            if (this.isLoaded) return;

            try {
                const response = await axios.get('/options');
                this.options = response.data;
                this.isLoaded = true;
            } catch (error) {
                console.error('Error fetching services:', error);
            }
        }
    }
});
