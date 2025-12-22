<template>
    <div class="savings-container">
        <div v-if="blocks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="block in blocks"
                :key="block.id"
                class="savings-card group h-full flex flex-col relative overflow-hidden rounded-2xl bg-white/20 dark:bg-white/[0.02] backdrop-blur-xl border border-black/10 dark:border-white/[0.08] hover:border-black/20 dark:hover:border-white/[0.15] transition-all duration-500 shadow-lg before:absolute before:inset-0 before:bg-gradient-to-br before:from-white/60 dark:before:from-white/[0.08] before:to-transparent before:pointer-events-none"
            >
                <div class="relative p-6 flex flex-col items-center text-center flex-1 bg-gradient-to-t from-black/[0.01] to-transparent dark:from-black/[0.02] backdrop-blur-sm z-10">
                    <img
                        v-if="block.logo"
                        :src="block.logo"
                        :alt="block.title"
                        width="64"
                        height="64"
                        class="w-16 h-16 mb-4 object-contain relative z-10"
                        style="aspect-ratio: 1 / 1;"
                    />
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white relative z-10">{{ block.title }}</h3>
                    <p v-if="block.text" class="text-gray-600 dark:text-gray-300 mb-4 text-sm relative z-10">
                        {{ block.text }}
                    </p>
                    <div v-if="block.our_price || block.normal_price" class="mb-4 relative z-10">
                        <div v-if="block.our_price" class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ block.our_price }}
                        </div>
                        <div v-if="block.normal_price" class="text-sm text-gray-500 dark:text-gray-400 line-through">
                            {{ block.normal_price }}
                        </div>
                    </div>
                    <p v-if="block.advantage" class="text-sm text-gray-700 dark:text-gray-300 relative z-10">
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

.savings-card {
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.savings-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

:global(.dark) .savings-card:hover {
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
}

@media (hover: none) and (pointer: coarse) {
    .savings-card:hover {
        transform: none;
    }
}
</style>

