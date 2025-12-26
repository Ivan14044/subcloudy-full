<template>
    <div class="reviews-container">
        <!-- SVG фильтр для эффекта матового стекла -->
        <svg style="display: none" class="liquid-glass-filter">
            <filter
                id="glass-distortion"
                x="0%"
                y="0%"
                width="100%"
                height="100%"
                filterUnits="objectBoundingBox"
            >
                <feTurbulence
                    type="fractalNoise"
                    baseFrequency="0.01 0.01"
                    numOctaves="1"
                    seed="5"
                    result="turbulence"
                />
                <feComponentTransfer in="turbulence" result="mapped">
                    <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
                    <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
                    <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
                </feComponentTransfer>
                <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />
                <feSpecularLighting
                    in="softMap"
                    surfaceScale="5"
                    specularConstant="1"
                    specularExponent="100"
                    lighting-color="white"
                    result="specLight"
                >
                    <fePointLight x="-200" y="-200" z="300" />
                </feSpecularLighting>
                <feComposite
                    in="specLight"
                    operator="arithmetic"
                    k1="0"
                    k2="1"
                    k3="1"
                    k4="0"
                    result="litImage"
                />
                <feDisplacementMap
                    in="SourceGraphic"
                    in2="softMap"
                    scale="150"
                    xChannelSelector="R"
                    yChannelSelector="G"
                />
            </filter>
        </svg>

        <swiper
            v-if="Array.isArray(reviews) && reviews.length > 0"
            :key="swiperKey"
            :modules="[Navigation, Pagination, Autoplay]"
            :slides-per-view="1"
            :space-between="30"
            :autoplay="{
                delay: 5000,
                disableOnInteraction: false
            }"
            :breakpoints="{
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }"
            :navigation="true"
            :pagination="{ clickable: true }"
            class="mt-8 px-6"
        >
            <swiper-slide v-for="(review, index) in (Array.isArray(reviews) ? reviews : [])" :key="`${review.id}-${index}`" class="pb-12">
                <div class="liquid-glass-wrapper review-card">
                    <div class="liquid-glass-effect"></div>
                    <div class="liquid-glass-tint"></div>
                    <div class="liquid-glass-shine"></div>
                    <div class="liquid-glass-text">
                        <div class="flex flex-row items-center mb-5">
                            <div class="relative">
                                <div class="w-[44px] h-[44px] rounded-full overflow-hidden mr-3">
                                    <img
                                        :src="review.photo || '/img/default-avatar.png'"
                                        alt="User avatar"
                                        width="44"
                                        height="44"
                                        class="w-full h-full object-cover"
                                        style="aspect-ratio: 1 / 1;"
                                    />
                                </div>
                                <img
                                    v-if="review.logo"
                                    :src="review.logo"
                                    width="24"
                                    height="24"
                                    style="aspect-ratio: 1 / 1;"
                                    alt="Company logo"
                                    class="w-[16px] h-[16px] object-cover absolute bottom-0 right-[8px] z-10 rounded-full bg-white border-2 border-white border-solid"
                                />
                            </div>
                            <div class="flex flex-col items-start">
                                <div class="font-medium text-base leading-5 m-0 text-gray-900 dark:text-white">
                                    {{ review.name }}
                                </div>
                                <div class="flex flex-row gap-0 mt-2">
                                    <svg
                                        v-for="i in 5"
                                        :key="i"
                                        width="15"
                                        height="14"
                                        viewBox="0 0 15 14"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M7.95212 10.0742L11.5816 12.25L10.6185 8.14919L13.8251 5.39002L9.60243 5.03419L7.95212 1.16669L6.3018 5.03419L2.0791 5.39002L5.28577 8.14919L4.32259 12.25L7.95212 10.0742Z"
                                            :fill="i <= review.rating ? '#FDCF2F' : '#E5E7EB'"
                                        ></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-base line-clamp-4">
                            {{ review.text }}
                        </p>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
        <div v-if="!isLoading && (!Array.isArray(reviews) || reviews.length === 0)" class="text-center py-8 text-gray-500">
            Нет отзывов для отображения
        </div>
        <div v-if="isLoading" class="text-center py-8 text-gray-500">
            Загрузка отзывов...
        </div>
    </div>
</template>

<script setup lang="ts">
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { locale } = useI18n();
const reviews = ref<any[]>([]);
const swiperKey = ref(Date.now());
const isLoading = ref(false);

// Загрузка отзывов
async function loadReviews() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    try {
        const currentLocale = locale.value || 'ru';
        console.log('[ReviewSection] Loading reviews for locale:', currentLocale);
        
        const response = await axios.get('/reviews', {
            params: { lang: currentLocale }
        });
        
        console.log('[ReviewSection] API response:', response.data);
        console.log('[ReviewSection] Response status:', response.status);
        
        if (response.data && response.data.success !== false) {
            const data = response.data.data || response.data || [];
            // Убеждаемся, что data - это массив
            if (Array.isArray(data)) {
                reviews.value = data;
            } else if (data && typeof data === 'object' && Array.isArray(Object.values(data))) {
                // Если data - объект с массивами, берем первый массив
                const firstArray = Object.values(data).find(item => Array.isArray(item));
                reviews.value = Array.isArray(firstArray) ? firstArray : [];
            } else {
                reviews.value = [];
            }
            console.log('[ReviewSection] Loaded reviews count:', reviews.value.length);
            console.log('[ReviewSection] Reviews data:', reviews.value);
            swiperKey.value = Date.now();
        } else {
            console.warn('[ReviewSection] API returned success=false or invalid response');
            reviews.value = [];
        }
    } catch (error: any) {
        console.error('[ReviewSection] Failed to load reviews:', error);
        console.error('[ReviewSection] Error details:', error.response?.data || error.message);
        console.error('[ReviewSection] Error response:', error.response);
        reviews.value = [];
    } finally {
        isLoading.value = false;
    }
}

// Загружаем при монтировании
onMounted(() => {
    loadReviews();
});

// Перезагружаем при смене языка
watch(() => locale.value, () => {
    loadReviews();
});

</script>

<style scoped>
/* Liquid Glass Effect для карточек отзывов */
.liquid-glass-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 0.75rem;
    padding: 1.25rem;
    height: 214px;
    max-height: 214px;
    box-shadow: 0 6px 6px rgba(0, 0, 0, 0.2), 0 0 20px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 2.2);
    cursor: pointer;
}

.liquid-glass-wrapper:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.25), 0 0 30px rgba(0, 0, 0, 0.15);
}

.liquid-glass-effect {
    position: absolute;
    z-index: 0;
    inset: 0;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    filter: url(#glass-distortion);
    overflow: hidden;
    isolation: isolate;
    border-radius: 0.75rem;
}

.liquid-glass-tint {
    z-index: 1;
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 0.75rem;
}

.dark .liquid-glass-tint {
    background: rgba(31, 41, 55, 0.4);
}

.liquid-glass-shine {
    position: absolute;
    inset: 0;
    z-index: 2;
    overflow: hidden;
    border-radius: 0.75rem;
    box-shadow: 
        inset 2px 2px 1px 0 rgba(255, 255, 255, 0.5),
        inset -1px -1px 1px 1px rgba(255, 255, 255, 0.5);
    pointer-events: none;
}

.dark .liquid-glass-shine {
    box-shadow: 
        inset 2px 2px 1px 0 rgba(255, 255, 255, 0.1),
        inset -1px -1px 1px 1px rgba(255, 255, 255, 0.1);
}

.liquid-glass-text {
    z-index: 3;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.liquid-glass-filter {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
}

/* Стили для Swiper */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
    @apply text-gray-800 bg-white/50 dark:bg-gray-600 dark:text-white hover:bg-[#0047ff] hover:text-white dark:hover:bg-gray-700 transition-all duration-300 w-10 h-10 rounded-full flex items-center justify-center top-[45%];
    backdrop-filter: blur(4px);
}

:deep(.swiper-button-prev) {
    @apply left-0;
}

:deep(.swiper-button-next) {
    @apply right-0;
}

:deep(.swiper-button-disabled) {
    @apply opacity-0;
}

:deep(.swiper-button-next)::after,
:deep(.swiper-button-prev)::after {
    @apply text-sm;
}

:deep(.swiper-pagination-bullet) {
    @apply w-1.5 h-1.5;
}

:deep(.swiper-pagination-bullet-active) {
    @apply bg-gray-800;
}
</style>

