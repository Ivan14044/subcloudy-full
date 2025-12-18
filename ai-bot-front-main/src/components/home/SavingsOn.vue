<template>
    <div class="savings-container">
        <div v-if="blocks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="block in blocks"
                :key="block.id"
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-shadow"
            >
                <div class="flex flex-col items-center text-center">
                    <img
                        v-if="block.logo"
                        :src="block.logo"
                        :alt="block.title"
                        width="64"
                        height="64"
                        class="w-16 h-16 mb-4 object-contain"
                        style="aspect-ratio: 1 / 1;"
                    />
                    <h3 class="text-xl font-bold mb-2 dark:text-white">{{ block.title }}</h3>
                    <p v-if="block.text" class="text-gray-600 dark:text-gray-400 mb-4 text-sm">
                        {{ block.text }}
                    </p>
                    <div v-if="block.our_price || block.normal_price" class="mb-4">
                        <div v-if="block.our_price" class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ block.our_price }}
                        </div>
                        <div v-if="block.normal_price" class="text-sm text-gray-500 line-through">
                            {{ block.normal_price }}
                        </div>
                    </div>
                    <p v-if="block.advantage" class="text-sm text-gray-700 dark:text-gray-300">
                        {{ block.advantage }}
                    </p>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            Нет блоков экономии для отображения
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { locale } = useI18n();
const blocks = ref<any[]>([]);
const isLoading = ref(false);

// Загрузка блоков экономии
async function loadBlocks() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get('/savings-blocks', {
            params: { lang: locale.value }
        });
        
        if (response.data.success) {
            blocks.value = response.data.data || [];
        }
    } catch (error) {
        console.error('Failed to load savings blocks:', error);
        blocks.value = [];
    } finally {
        isLoading.value = false;
    }
}

// Загружаем при монтировании
onMounted(() => {
    loadBlocks();
});

// Перезагружаем при смене языка
watch(() => locale.value, () => {
    loadBlocks();
});

</script>

<style scoped>
.savings-container {
    @apply w-full;
}
</style>

