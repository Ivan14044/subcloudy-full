<template>
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between relative mt-[30px] sm:mt-0 min-h-[350px] lg:min-h-[600px] xl:min-h-[600px]"
    >
        <div class="sm:max-w-[100%] md:max-w-[45%]">
            <div class="hero-content">
                <h1
                    class="text-[32px] md:text-[48px] lg:text-[64px] font-medium leading-none text-gray-900 dark:text-white mb-4"
                    v-html="$t('hero.title')"
                ></h1>
                <p
                    class="description text-gray-700 dark:text-gray-400 mb-6 md:mb-10 leading-6 text-lg"
                    style="contain: content;"
                    v-html="$t('hero.description')"
                ></p>
                <!-- SEO: Добавлен href для краулеров -->
                <a
                    href="#services"
                    class="cta-button pointer-events-auto cursor-pointer"
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
            <div
                ref="lottieContainer"
                class="lottie-animation-container"
            ></div>
        </div>
        <button
            class="scroll-to-services-btn glass-button text-dark dark:text-white/90 absolute bottom-0 sm:bottom-[10px] right-[50%] translate-x-[50%] pointer-events-auto cursor-pointer rounded-full w-10 h-10 flex items-center justify-center transition-none"
            :class="{ 'glass-dark': isDark, 'glass-light': !isDark }"
            aria-label="Scroll to services"
            @click.prevent="scrollToElement('#services')"
        >
            <svg
                class="w-5 h-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { scrollToElement } from '@/utils/scrollToElement';
import { useTheme } from '@/composables/useTheme';

const { isDark } = useTheme();
const lottieContainer = ref<HTMLElement | null>(null);
let animInstance: any = null;
let visibilityObserver: IntersectionObserver | null = null;

const loadAnimation = async () => {
    try {
        // Динамический импорт легкой версии библиотеки плеера
        const lottie = (await import('lottie-web/build/player/lottie_light.js')).default;
        
        // Загрузка данных анимации
        const data = isDark.value 
            ? await import('@/assets/Dark.json')
            : await import('@/assets/Light.json');

        if (lottieContainer.value) {
            if (animInstance) {
                animInstance.destroy();
            }
            
            // Убеждаемся, что контейнер имеет правильные размеры перед инициализацией
            const container = lottieContainer.value;
            
            // Определяем размер в зависимости от размера экрана
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
            const targetSize = isMobile ? 256 : isTablet ? 400 : 600; // 256px mobile, 400px tablet, 600px desktop
            
            // Принудительно устанавливаем размеры контейнера
            container.style.width = `${targetSize}px`;
            container.style.height = `${targetSize}px`;
            container.style.minWidth = `${targetSize}px`;
            container.style.minHeight = `${targetSize}px`;
            container.style.maxWidth = `${targetSize}px`;
            container.style.maxHeight = `${targetSize}px`;
            
            // Создаем MutationObserver для отслеживания создания SVG
            const observer = new MutationObserver((mutations) => {
                const svg = container.querySelector('svg');
                if (svg) {
                    // Используем заранее известный targetSize вместо offsetWidth для предотвращения принудительной компоновки
                    requestAnimationFrame(() => {
                        const size = targetSize;
                    
                        // Принудительно устанавливаем размеры SVG
                        svg.style.width = `${size}px`;
                        svg.style.height = `${size}px`;
                        svg.style.minWidth = `${size}px`;
                        svg.style.minHeight = `${size}px`;
                        svg.style.maxWidth = `${size}px`;
                        svg.style.maxHeight = `${size}px`;
                        svg.style.display = 'block';
                        
                        // Устанавливаем атрибуты SVG
                        const originalViewBox = svg.getAttribute('viewBox') || '0 0 1000 1000';
                        svg.setAttribute('width', size.toString());
                        svg.setAttribute('height', size.toString());
                        svg.setAttribute('viewBox', originalViewBox);
                        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
                        
                        // Вызываем resize для обновления размеров
                        if (animInstance && typeof animInstance.resize === 'function') {
                            animInstance.resize(size, size);
                        }
                    });
                    
                    // Отключаем observer после применения размеров
                    observer.disconnect();
                }
            });
            
            // Начинаем наблюдение за изменениями в контейнере
            observer.observe(container, {
                childList: true,
                subtree: true
            });
            
                    animInstance = lottie.loadAnimation({
                        container: container,
                        renderer: 'svg',
                        loop: false,
                        autoplay: true,
                        animationData: data.default
                    });

                    // Оптимизация: пауза анимации, когда она вне зоны видимости
                    if (visibilityObserver) {
                        visibilityObserver.disconnect();
                    }
                    
                    visibilityObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (animInstance) {
                                if (entry.isIntersecting) {
                                    animInstance.play();
                                } else {
                                    animInstance.pause();
                                }
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    visibilityObserver.observe(container);

            // Функция для установки размеров SVG
            const setSvgSize = () => {
                const svg = container.querySelector('svg');
                if (svg) {
                    // Используем заранее известный targetSize
                    const size = targetSize;
                    
                    // Получаем оригинальный viewBox из данных анимации
                    const originalViewBox = svg.getAttribute('viewBox') || '0 0 1000 1000';
                    
                    // Принудительно устанавливаем размеры SVG через атрибуты
                    svg.setAttribute('width', size.toString());
                    svg.setAttribute('height', size.toString());
                    svg.setAttribute('viewBox', originalViewBox);
                    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
                    
                    // Принудительно устанавливаем размеры SVG через стили
                    svg.style.width = `${size}px`;
                    svg.style.height = `${size}px`;
                    svg.style.minWidth = `${size}px`;
                    svg.style.minHeight = `${size}px`;
                    svg.style.maxWidth = `${size}px`;
                    svg.style.maxHeight = `${size}px`;
                    svg.style.display = 'block';
                    svg.style.position = 'absolute';
                    svg.style.top = '0';
                    svg.style.left = '0';
                    svg.style.boxSizing = 'border-box';
                    
                    // Вызываем resize для обновления размеров
                    if (animInstance && typeof animInstance.resize === 'function') {
                        animInstance.resize(size, size);
                    }
                    
                    observer.disconnect();
                    return true;
                }
                return false;
            };

            // Пытаемся установить размеры сразу после загрузки данных
            animInstance.addEventListener('data_ready', () => {
                requestAnimationFrame(() => {
                    setSvgSize();
                });
            });

            // Дополнительная проверка через небольшую задержку
            setTimeout(() => {
                if (!setSvgSize()) {
                    // Если SVG еще не создан, ждем еще немного
                    setTimeout(() => {
                        setSvgSize();
                        observer.disconnect();
                    }, 300);
                }
            }, 100);

            animInstance.addEventListener('complete', startSpin);
        }
    } catch (error) {
        console.error('Failed to load animation:', error);
    }
};

const containerRef = ref();

const startSpin = () => {
    containerRef.value?.classList.add('spin-reverse-slower');
};

watch(isDark, () => {
    loadAnimation();
});

        onMounted(() => {
            // Еще больше откладываем инициализацию анимации (с 1.5 до 2.5 сек), 
            // чтобы дать приоритет отрисовке текста (LCP)
            setTimeout(loadAnimation, 2500);
        });

        onBeforeUnmount(() => {
            if (animInstance) {
                animInstance.destroy();
                animInstance = null;
            }
            if (visibilityObserver) {
                visibilityObserver.disconnect();
                visibilityObserver = null;
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

/* Гарантируем правильные размеры для контейнера Lottie */
.lottie-animation-container {
    flex-shrink: 0;
    position: relative;
    overflow: visible;
    /* Размеры устанавливаются через JavaScript для точного контроля */
}

/* Гарантируем, что SVG внутри контейнера занимает весь размер */
.lottie-animation-container :deep(svg) {
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    display: block !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    box-sizing: border-box !important;
    transform-origin: top left;
}

/* Гарантируем, что все дочерние элементы SVG также масштабируются */
.lottie-animation-container :deep(svg > *) {
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

/* Стиль кнопки "Scroll to services" - минималистичный, как на карточках сервисов */
.scroll-to-services-btn {
    /* Базовый стиль уже применен через glass-button */
}

.scroll-to-services-btn:hover {
    opacity: 0.8;
}

.scroll-to-services-btn:active {
    opacity: 0.7;
}

/* Дополнительные стили для светлой темы */
.scroll-to-services-btn.glass-light {
    /* Стиль уже определен в app.css через .glass-button */
}

/* Дополнительные стили для темной темы */
.scroll-to-services-btn.glass-dark {
    background: rgba(12, 24, 60, 0.24);
    border-color: rgba(255, 255, 255, 0.06);
}
</style>
