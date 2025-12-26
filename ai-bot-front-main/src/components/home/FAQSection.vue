<template>
    <div class="faq-section">
        <!-- SVG фильтр для эффекта матового стекла -->
        <svg style="display: none" class="liquid-glass-filter">
            <filter
                id="faq-glass-distortion"
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

        <div
            v-if="faqItems.length > 0"
            itemscope
            itemtype="https://schema.org/FAQPage"
            class="max-w-4xl mx-auto"
        >
            <div
                v-for="(item, index) in faqItems"
                :key="index"
                itemprop="mainEntity"
                itemscope
                itemtype="https://schema.org/Question"
                class="faq-item mb-4"
            >
                <!-- SEO Metadata (Hidden from user, but visible to crawlers) -->
                <div style="display: none;">
                    <span itemprop="name">{{ item.question }}</span>
                    <div itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                        <div itemprop="text">{{ item.answer }}</div>
                    </div>
                </div>

                <div
                    class="liquid-glass-wrapper faq-question-wrapper rounded-lg overflow-hidden transition-all duration-300"
                    :class="{ 'faq-item-open': openIndex === index }"
                >
                    <div class="liquid-glass-effect"></div>
                    <div class="liquid-glass-tint"></div>
                    <div class="liquid-glass-shine"></div>
                    <div class="liquid-glass-text">
                        <button
                            class="faq-question w-full px-6 py-4 text-left flex items-center justify-between transition-colors duration-200"
                            @click="toggleItem(index)"
                            type="button"
                            :aria-expanded="openIndex === index"
                        >
                            <span class="font-medium text-lg text-gray-900 dark:text-white pr-4">
                                {{ item.question }}
                            </span>
                            <svg
                                class="w-5 h-5 text-gray-600 dark:text-gray-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openIndex === index }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <Transition
                            name="faq-answer"
                            @enter="onEnter"
                            @after-enter="onAfterEnter"
                            @leave="onLeave"
                            @after-leave="onAfterLeave"
                        >
                            <div
                                v-if="openIndex === index"
                                class="faq-answer-wrapper overflow-hidden"
                            >
                                <div
                                    class="faq-answer px-6 pb-4 text-gray-700 dark:text-gray-300 leading-relaxed"
                                >
                                    <div>
                                        {{ item.answer }}
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isLoading" class="text-center py-8 text-gray-500">
            Загрузка FAQ...
        </div>

        <div v-if="!isLoading && faqItems.length === 0" class="text-center py-8 text-gray-500">
            FAQ пока не добавлены
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

interface FAQItem {
    question: string;
    answer: string;
}

const { locale } = useI18n();
const faqItems = ref<FAQItem[]>([]);
const openIndex = ref<number | null>(null);
const isLoading = ref(false);

// Анимация раскрытия - оптимизирована для плавности
const onEnter = (el: Element) => {
    const element = el as HTMLElement;
    element.style.height = '0';
    element.style.opacity = '0';
    element.style.transition = 'height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1)';
};

const onAfterEnter = (el: Element) => {
    const element = el as HTMLElement;
    const height = element.scrollHeight;
    // Используем requestAnimationFrame для плавного перехода
    requestAnimationFrame(() => {
        element.style.height = height + 'px';
        element.style.opacity = '1';
    });
};

const onLeave = (el: Element) => {
    const element = el as HTMLElement;
    const height = element.scrollHeight;
    element.style.height = height + 'px';
    element.style.opacity = '1';
    element.style.transition = 'height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1)';
    // Используем requestAnimationFrame для плавного перехода
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            element.style.height = '0';
            element.style.opacity = '0';
        });
    });
};

const onAfterLeave = (el: Element) => {
    const element = el as HTMLElement;
    element.style.height = '';
    element.style.opacity = '';
    element.style.transition = '';
};

const toggleItem = (index: number) => {
    if (openIndex.value === index) {
        openIndex.value = null;
    } else {
        openIndex.value = index;
    }
};

const loadFAQ = async () => {
    if (isLoading.value) return;
    
    isLoading.value = true;
    try {
        const currentLocale = locale.value || 'ru';
        const response = await axios.get('/content/faq', {
            params: { lang: currentLocale }
        });
        
        if (response.data && response.data.success !== false) {
            const data = response.data.data || [];
            faqItems.value = Array.isArray(data) ? data : [];
        } else {
            faqItems.value = [];
        }
    } catch (error: any) {
        console.error('[FAQSection] Failed to load FAQ:', error);
        faqItems.value = [];
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadFAQ();
});

watch(() => locale.value, () => {
    loadFAQ();
    openIndex.value = null; // Закрываем открытый элемент при смене языка
});
</script>

<style scoped>
.faq-section {
    width: 100%;
}

.faq-item {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Liquid Glass Effect для FAQ */
.liquid-glass-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    font-weight: 600;
    overflow: hidden;
    color: black;
    cursor: default;
    box-shadow: 0 6px 6px rgba(0, 0, 0, 0.2), 0 0 20px rgba(0, 0, 0, 0.1);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 2.2);
    border-radius: 0.75rem; /* rounded-lg */
}

.liquid-glass-wrapper:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.25), 0 0 30px rgba(0, 0, 0, 0.15);
}

.liquid-glass-wrapper.faq-item-open {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.2);
}

.liquid-glass-effect {
    position: absolute;
    z-index: 0;
    inset: 0;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    filter: url(#faq-glass-distortion);
    overflow: hidden;
    isolation: isolate;
    border-radius: 0.75rem; /* rounded-lg */
}

.liquid-glass-tint {
    z-index: 1;
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 0.75rem; /* rounded-lg */
}

.dark .liquid-glass-tint {
    background: rgba(31, 41, 55, 0.6);
}

.liquid-glass-shine {
    position: absolute;
    inset: 0;
    z-index: 2;
    overflow: hidden;
    border-radius: 0.75rem; /* rounded-lg */
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
    width: 100%;
}

.liquid-glass-filter {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
}

.faq-answer-wrapper {
    transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.faq-answer-enter-active,
.faq-answer-leave-active {
    transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.faq-answer-enter-from,
.faq-answer-leave-to {
    height: 0 !important;
    opacity: 0;
}

.faq-answer {
    white-space: pre-line;
    line-height: 1.7;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 640px) {
    .faq-question {
        padding: 1rem;
        font-size: 1rem;
    }

    .faq-answer {
        padding: 0 1rem 1rem;
        font-size: 0.95rem;
    }
}
</style>
