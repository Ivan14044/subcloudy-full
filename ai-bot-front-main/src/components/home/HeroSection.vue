<template>
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between relative mt-[30px] sm:mt-0 min-h-[350px] lg:min-h-[600px] xl:min-h-[600px]"
    >
        <div class="sm:max-w-[100%] md:max-w-[45%] pointer-events-none">
            <div class="hero-content">
                <h1
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium leading-none text-gray-900 dark:text-white mb-4"
                    v-html="$t('hero.title')"
                ></h1>
                <p
                    class="description text-gray-700 dark:text-gray-400 mb-6 md:mb-10 leading-6 text-lg"
                    v-html="$t('hero.description')"
                ></p>
                <a
                    class="cta-button dark:border-gray-300 dark:text-white dark:hover:border-blue-900 pointer-events-auto cursor-pointer"
                    @click.prevent="scrollToElement('#services')"
                >
                    {{ $t('hero.button') }}
                </a>
            </div>
        </div>
        <div
            ref="containerRef"
            class="w-full sm:w-auto flex justify-center pointer-events-none mt-[-65px]"
        >
            <Vue3Lottie
                :key="animationData"
                :animation-data="animationData"
                :loop="false"
                :autoplay="true"
                class="w-64 h-64"
                @on-complete="startSpin"
            />
        </div>
        <button
            class="dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-300 absolute bottom-0 sm:bottom-[10px] right-[50%] translate-x-[50%] pointer-events-auto cursor-pointer rounded-full shadow w-10 h-10 flex items-center justify-center transition hover:bg-gray-100"
            aria-label="Scroll to services"
            @click.prevent="scrollToElement('#services')"
        >
            <svg
                class="w-4 h-4 text-blue-600 dark:text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { scrollToElement } from '@/utils/scrollToElement';
import { useTheme } from '@/composables/useTheme';
import animationLight from '@/assets/Light.json';
import animationDark from '@/assets/Dark.json';

const { isDark } = useTheme();
const animationData = computed(() => (isDark.value ? animationDark : animationLight));

const containerRef = ref();
const lottieRef = ref<any>(null);

const startSpin = () => {
    containerRef.value?.classList.add('spin-reverse-slower');
};

watch(isDark, dark => {
    if (lottieRef.value) {
        lottieRef.value.stop();
        lottieRef.value.load(animationData.value);
        lottieRef.value.play();
    }
});
</script>

<style scoped>
@keyframes spin-reverse-slower {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(-360deg);
    }
}

.spin-reverse-slower {
    animation: spin-reverse-slower 180s linear infinite;
    transform-origin: center;
}

.hero-content {
    text-align: center;
}

@media (min-width: 640px) {
    .hero-content {
        text-align: left;
    }
}
</style>
