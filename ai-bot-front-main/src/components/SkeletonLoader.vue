<template>
    <div :class="['skeleton-loader', containerClass]" :style="containerStyle">
        <div v-for="i in lines" :key="i" class="skeleton-line" :style="lineStyle"></div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    lines?: number;
    width?: string;
    height?: string;
    containerClass?: string;
}>(), {
    lines: 3,
    width: '100%',
    height: '20px',
    containerClass: ''
});

const containerStyle = computed(() => ({
    minHeight: props.height
}));

const lineStyle = computed(() => ({
    width: props.width,
    height: props.height
}));
</script>

<style scoped>
.skeleton-loader {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px;
}

.skeleton-line {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
}

.dark .skeleton-line {
    background: linear-gradient(90deg, #2a2a2a 25%, #3a3a3a 50%, #2a2a2a 75%);
    background-size: 200% 100%;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .skeleton-line {
        animation: none;
        background: #f0f0f0;
    }
    .dark .skeleton-line {
        background: #2a2a2a;
    }
}
</style>

