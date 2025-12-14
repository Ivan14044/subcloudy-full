<template>
    <div
        ref="cardRef"
        class="service-card relative group rounded-3xl transform-gpu"
        :class="{ 'theme-dark': isDark, 'theme-light': !isDark, flipped: isFlipped }"
        :style="[tiltStyle, containerHeightStyle]"
        tabindex="0"
        @pointermove.passive="onPointerMove"
        @pointerenter="onPointerEnter"
        @pointerleave="onPointerLeave"
        @pointerdown="onPointerDown"
        @pointerup="onPointerUp"
        @keydown.space.prevent="flipCard"
        @keydown.enter.prevent="flipCard"
        @click="onBackClick()"
    >
        <div
            ref="innerRef"
            class="card-inner relative w-full"
            :class="{ flipped: isFlipped }"
            role="group"
            :aria-expanded="isFlipped.toString()"
        >
            <section
                ref="frontRef"
                class="card-side front p-6 text-center"
                :aria-hidden="isFlipped.toString()"
            >
                <div class="flex justify-center items-center mt-3 mb-6">
                    <div
                        class="w-24 h-24 rounded-2xl flex items-center justify-center text-2xl shadow-lg transition-all duration-300 group-hover:scale-105 glass-icon"
                    >
                        <img
                            :src="service.logo"
                            :alt="`${service.name} Logo`"
                            class="w-20 h-20 object-contain"
                            loading="lazy"
                            @load="updateContainerHeight"
                        />
                    </div>
                </div>

                <h3 class="text-xl font-medium mb-1 text-dark dark:text-white">
                    {{ serviceName }}
                </h3>
                <p class="text-sm mb-4 test-white h-[20px]">
                    <span v-if="serviceSubtitle">{{
                        serviceSubtitle
                    }}</span>
                </p>

                <p
                    class="text-sm font-medium mb-4 text-dark dark:text-white"
                >
                    ${{ service.amount.toFixed(2) }} USD/month
                </p>

                <div class="mb-6 max-w-xs mx-auto text-left relative">
                    <p
                        class="line-clamp-3 text-sm test-white"
                        v-html="serviceDescription"
                    ></p>
                    <div
                        class="fade-bottom"
                        :class="{ 'fade-dark': isDark, 'fade-light': !isDark }"
                    ></div>

                    <div class="mt-3 flex justify-center relative z-10">
                        <button
                            class="show-more-button text-sm font-medium text-blue-500 hover:text-blue-600 transition-colors focus:outline-none relative z-10"
                            :aria-expanded="isFlipped.toString()"
                            type="button"
                            @click.stop="flipCard()"
                        >
                            {{ $t('services.card.show_more') }}
                        </button>
                    </div>
                </div>

                <div
                    class="flex flex-col sm:flex-row gap-3 justify-center items-stretch w-full max-w-3xl mx-auto"
                >
                    <!-- Кнопка "Открыть сервис" -->
                    <button
                        class="animated-button flex-1 px-4 py-2.5 rounded-full font-medium text-[15px] transition-all duration-300 overflow-hidden flex items-center justify-center gap-2 btn-success"
                        @click.stop="openService(service.id)"
                    >
                        {{ $t('services.openService') }}
                    </button>
                </div>
            </section>

            <section
                ref="backRef"
                class="card-side back p-6"
                :aria-hidden="(!isFlipped).toString()"
                @click="onBackClick()"
            >
                <div class="h-full flex flex-col">
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="w-12 h-12 rounded-lg flex items-center justify-center glass-icon"
                        >
                            <img :src="service.logo" class="w-8 h-8 object-contain" alt="" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-dark dark:text-white">
                                {{ serviceName }}
                            </h3>
                            <div class="text-sm text-dark dark:text-white">
                                {{ serviceSubtitle }}
                            </div>
                        </div>
                        <div class="text-sm font-medium ml-auto text-dark dark:text-white">
                            ${{ service.amount.toFixed(2) }} USD
                        </div>
                    </div>

                    <div class="flex-1 overflow-auto mb-4">
                        <div
                            class="typed-content text-sm text-center leading-relaxed text-dark dark:text-white"
                            v-html="serviceDescription"
                        ></div>
                    </div>

                    <div class="text-xs text-center text-dark dark:text-white/80">
                        {{ $t('services.card.back_to_front_side') }}
                    </div>
                </div>
            </section>

            <div class="absolute inset-0 rounded-3xl pointer-events-none overlay-wrap z-0">
                <div class="absolute inset-0 rounded-3xl glass-overlay"></div>
                <div
                    class="absolute inset-0 rounded-3xl glass-shine"
                    :style="{ '--shine-x': shineX, '--shine-y': shineY }"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDarkMode } from '../../composables/useDarkMode';

interface Props {
    service: {
        id: number;
        logo: string;
        name: string;
        amount: number;
        translations?: any;
        [key: string]: any;
    };
}

const props = defineProps<Props>();
const emit = defineEmits(['launch']);

const { locale } = useI18n();
const { isDark } = useDarkMode();

// Computed properties for localized content
const serviceName = computed(() => {
    return props.service.translations?.[locale.value]?.name || props.service.name;
});

const serviceSubtitle = computed(() => {
    return props.service.translations?.[locale.value]?.subtitle || props.service.translations?.en?.subtitle || '';
});

const serviceDescription = computed(() => {
    return props.service.translations?.[locale.value]?.short_description_card 
        || props.service.translations?.en?.short_description_card 
        || 'Premium service access';
});

const cardRef = ref<HTMLElement | null>(null);
const innerRef = ref<HTMLElement | null>(null);
const frontRef = ref<HTMLElement | null>(null);
const backRef = ref<HTMLElement | null>(null);

const isFlipped = ref(false);
const frontVisible = ref(true);

const isMobile = ref(false);

const targetX = ref(0.5);
const targetY = ref(0.5);
const displayX = ref(0.5);
const displayY = ref(0.5);
let rafId: number | null = null;

const containerHeight = ref<number | null>(null);
const containerHeightStyle = computed(() =>
    containerHeight.value ? { height: `${containerHeight.value}px` } : { height: 'auto' }
);

const shineX = computed(() => `${(displayX.value * 100).toFixed(2)}%`);
const shineY = computed(() => `${(displayY.value * 100).toFixed(2)}%`);

const baseMaxTilt = 4;
const tiltStyle = computed(() => {
    if (isMobile.value) return { transform: '', '--shine-x': '50%', '--shine-y': '50%' } as any;
    const dx = Math.abs(displayX.value - 0.5);
    const dy = Math.abs(displayY.value - 0.5);
    const distanceFromCenter = Math.sqrt(dx * dx + dy * dy);
    const minFactor = 0.85;
    const cornerFactor = 1 + distanceFromCenter * 0.9;
    const finalFactor = Math.max(minFactor, cornerFactor);
    const rotateX = (0.5 - displayY.value) * baseMaxTilt * finalFactor;
    const rotateY = (displayX.value - 0.5) * baseMaxTilt * finalFactor;
    const translateZ = isFlipped.value ? 0 : 10;

    return {
        transform: `perspective(1100px) rotateX(${rotateX.toFixed(3)}deg) rotateY(${rotateY.toFixed(3)}deg) translateZ(${translateZ}px)`,
        '--shine-x': shineX.value,
        '--shine-y': shineY.value
    } as any;
});

const tick = () => {
    displayX.value += (targetX.value - displayX.value) * 0.12;
    displayY.value += (targetY.value - displayY.value) * 0.12;
    rafId = requestAnimationFrame(tick);
};

const onPointerMove = (e: PointerEvent) => {
    if (!cardRef.value || isMobile.value) return;
    const rect = cardRef.value.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    targetX.value = Math.max(0, Math.min(1, x));
    targetY.value = Math.max(0, Math.min(1, y));
    displayX.value = targetX.value;
    displayY.value = targetY.value;
};

const onPointerEnter = () => {
    if (cardRef.value) cardRef.value.classList.add('is-active');
};

const onPointerLeave = () => {
    targetX.value = 0.5;
    targetY.value = 0.5;
    if (cardRef.value) cardRef.value.classList.remove('is-active');
};

const onPointerDown = (e: PointerEvent) => {
    (e.target as Element).setPointerCapture?.(e.pointerId);
};

const onPointerUp = (e: PointerEvent) => {
    (e.target as Element).releasePointerCapture?.(e.pointerId);
};

const updateContainerHeight = async () => {
    await nextTick();
    const frontEl = frontRef.value as HTMLElement | null;
    const backEl = backRef.value as HTMLElement | null;
    const frontH = frontEl ? frontEl.scrollHeight : 0;
    const backH = backEl ? backEl.scrollHeight : 0;
    const desired = Math.max(frontH, backH);
    containerHeight.value = desired > 0 ? Math.ceil(desired) : null;
};

let innerTransitionHandler: ((e: TransitionEvent) => void) | null = null;
let transitionFallbackId: number | null = null;
const TRANSITION_EXPECTED_MS = 950;

const clearTransitionFallback = () => {
    if (transitionFallbackId !== null) {
        clearTimeout(transitionFallbackId);
        transitionFallbackId = null;
    }
};

const scheduleFallback = () => {
    clearTransitionFallback();
    transitionFallbackId = window.setTimeout(() => {
        frontVisible.value = !isFlipped.value;
        transitionFallbackId = null;
        requestAnimationFrame(updateContainerHeight);
    }, TRANSITION_EXPECTED_MS);
};

const onInnerTransitionEnd = (e?: TransitionEvent | Event) => {
    if (e && 'propertyName' in e) {
        const te = e as TransitionEvent;
        if (!te.propertyName || te.propertyName.indexOf('transform') === -1) return;
    }
    clearTransitionFallback();
    frontVisible.value = !isFlipped.value;
    requestAnimationFrame(updateContainerHeight);
};

const flipCard = async () => {
    isFlipped.value = !isFlipped.value;
    if (innerRef.value) frontVisible.value = false;
    scheduleFallback();
    await nextTick();
    requestAnimationFrame(updateContainerHeight);
};

const onBackClick = () => {
    if (isFlipped.value) flipCard();
};

const openService = async (id: number) => {
    console.log('[ServiceCard] Opening service:', id);
    emit('launch', id);
};

const handleResize = () => {
    isMobile.value = window.innerWidth < 768 || 'ontouchstart' in window;
    if (isMobile.value) {
        targetX.value = 0.5;
        targetY.value = 0.5;
        displayX.value = 0.5;
        displayY.value = 0.5;
    }
};

onMounted(() => {
    handleResize();
    rafId = requestAnimationFrame(tick);
    updateContainerHeight();

    if (innerRef.value) {
        innerTransitionHandler = onInnerTransitionEnd as (e: TransitionEvent) => void;
        innerRef.value.addEventListener('transitionend', innerTransitionHandler);
        innerRef.value.addEventListener('webkitTransitionEnd', innerTransitionHandler as (e: Event) => void);
    }

    window.addEventListener('resize', handleResize, { passive: true });
    window.addEventListener('resize', updateContainerHeight, { passive: true });
    setTimeout(updateContainerHeight, 160);
});

onUnmounted(() => {
    if (rafId !== null) {
        cancelAnimationFrame(rafId);
        rafId = null;
    }
    clearTransitionFallback();
    if (innerRef.value && innerTransitionHandler) {
        innerRef.value.removeEventListener('transitionend', innerTransitionHandler);
        innerRef.value.removeEventListener('webkitTransitionEnd', innerTransitionHandler as (e: Event) => void);
    }
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('resize', updateContainerHeight);
});
</script>

<style scoped>
.service-card {
    position: relative;
    z-index: 1;
    isolation: isolate;
    will-change: transform;
    width: 100%;
}

.service-card.theme-light .card-side {
    background: rgba(255, 255, 255, 0.4);
    border: 1px solid rgba(2, 6, 23, 0.05);
    color: #0b1220;
    box-shadow: 0 8px 30px rgba(10, 12, 25, 0.06);
}

.service-card.theme-dark .card-side {
    background: rgba(10, 14, 24, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.04);
    color: #ffffff;
    box-shadow: 0 10px 40px rgba(1, 4, 10, 0.55);
}

.service-card.is-active,
.service-card:hover,
.service-card:focus-within {
    z-index: 80;
}

.card-inner {
    width: 100%;
    transition: transform 0.85s cubic-bezier(0.22, 0.9, 0.36, 1);
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
    position: relative;
    z-index: 2;
    backface-visibility: hidden;
    will-change: transform;
}

.card-side {
    box-sizing: border-box;
    padding: 1.5rem;
    border-radius: 1rem;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    transform-style: preserve-3d;
    display: block;
    transition: opacity 0.35s ease;
    width: 100%;
    height: 100%;
}

.card-side.front {
    position: relative;
    transform: rotateY(0deg);
    z-index: 3;
}

.card-side.back {
    position: absolute;
    inset: 0;
    transform: rotateY(180deg);
    width: 100%;
    z-index: 2;
}

.card-inner.flipped {
    transform: rotateY(180deg);
}

.card-inner.flipped .card-side.front {
    opacity: 0;
    pointer-events: none;
    z-index: 1;
}

.card-inner.flipped .card-side.back {
    opacity: 1;
    pointer-events: auto;
    z-index: 4;
}

.service-card.flipped {
    z-index: 80;
}

.overlay-wrap {
    pointer-events: none;
    z-index: 0;
    border-radius: 1rem;
    overflow: hidden;
}

.service-card .glass-overlay {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.04);
}

.service-card:hover .glass-overlay,
.service-card.is-active .glass-overlay {
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    background: rgba(255, 255, 255, 0.035);
    border-color: rgba(255, 255, 255, 0.06);
    box-shadow: 0 12px 36px rgba(10, 12, 25, 0.12);
}

.service-card .glass-shine {
    pointer-events: none;
    mix-blend-mode: overlay;
    background: radial-gradient(
        600px circle at var(--shine-x, 50%) var(--shine-y, 50%),
        rgba(255, 255, 255, 0.14),
        rgba(255, 255, 255, 0.06) 20%,
        rgba(255, 255, 255, 0.02) 45%,
        transparent 60%
    );
    transition:
        opacity 160ms linear,
        background-position 120ms linear;
    opacity: 0.95;
}

.glass-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.15));
}

.animated-button {
    position: relative;
    overflow: hidden;
    z-index: 30;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}

.animated-button::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
    transition: all 0.6s ease;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    pointer-events: none;
}

.animated-button:hover::before {
    width: 300px;
    height: 300px;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 8px 18px;
    border-radius: 9999px;
}

.btn-success:hover {
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.fade-bottom {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 2rem;
    pointer-events: none;
    display: none;
}

.show-more-button {
    cursor: pointer;
    user-select: none;
}

.show-more-button:hover {
    text-decoration: underline;
}

.fade-light {
    background: linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.85));
}

.fade-dark {
    background: linear-gradient(to bottom, transparent, rgba(17, 24, 39, 0.85));
}

@media (max-width: 768px) {
    .service-card {
        transform: none !important;
    }

    .glass-overlay {
        backdrop-filter: blur(10px);
    }
}
</style>


