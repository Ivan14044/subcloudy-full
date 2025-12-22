<template>
    <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Загрузка -->
        <div v-if="isLoading" class="text-center">
            <div class="text-gray-500 dark:text-gray-400">Загрузка...</div>
        </div>

        <!-- Контент страницы -->
        <div v-else-if="pageData" class="content-page">
            <!-- Заголовок -->
            <div class="text-center mb-16">
                <h1 class="text-4xl font-light text-gray-900 dark:text-white mt-3">
                    {{ pageData.title }}
                </h1>
            </div>

            <!-- Содержимое -->
            <div 
                class="text-gray-900 dark:text-gray-300 prose prose-lg dark:prose-invert max-w-none"
                v-html="pageData.content"
            ></div>
        </div>

        <!-- Страница не найдена -->
        <div v-else class="text-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Страница не найдена
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Запрашиваемая страница не существует.
            </p>
            <router-link 
                to="/" 
                class="text-blue-500 hover:text-blue-600 underline dark:text-blue-400 dark:hover:text-blue-300"
            >
                Вернуться на главную
            </router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { usePageStore } from '@/stores/pages';
import { updateWebPageSEO } from '@/utils/seo';
import i18n from '@/i18n';

// Константы
const SUPPORTED_LOCALES = ['ru', 'uk', 'en', 'es', 'zh'] as const;
const DEFAULT_LOCALE = 'ru';

// Composables
const route = useRoute();
const pageStore = usePageStore();

// Состояние
const isLoading = ref(false);
const pageData = ref<{ title: string; content: string } | null>(null);

/**
 * Получает текущую локаль из i18n или localStorage
 */
const currentLocale = computed(() => {
    try {
        if (i18n?.global?.locale) {
            const localeValue = typeof i18n.global.locale === 'object' && i18n.global.locale.value
                ? i18n.global.locale.value
                : i18n.global.locale;
            
            if (localeValue && SUPPORTED_LOCALES.includes(localeValue as any)) {
                return localeValue;
            }
        }
    } catch (e) {
        // Игнорируем ошибки
    }
    
    try {
        const savedLang = localStorage.getItem('user-language');
        if (savedLang && SUPPORTED_LOCALES.includes(savedLang as any)) {
            return savedLang;
        }
    } catch (e) {
        // Игнорируем ошибки
    }
    
    return DEFAULT_LOCALE;
});

/**
 * Извлекает slug из пути маршрута
 */
function extractSlugFromPath(path: string): string {
    const slug = path.replace(/^\/|\/$/g, '');
    const segments = slug.split('/').filter(Boolean);
    
    if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0] as any)) {
        segments.shift();
    }
    
    return segments.join('/');
}

/**
 * Извлекает данные страницы для текущей локали
 */
function extractPageData(page: any, locale: string): { title: string; content: string } | null {
    if (!page || typeof page !== 'object') {
        return null;
    }

    // Legacy формат (плоские данные)
    if (page.title !== undefined || page.content !== undefined) {
        return {
            title: String(page.title || ''),
            content: String(page.content || '')
        };
    }

    // Пробуем получить данные для текущей локали
    if (page[locale] && typeof page[locale] === 'object') {
        const localeData = page[locale];
        if (localeData.title !== undefined || localeData.content !== undefined) {
            return {
                title: String(localeData.title || ''),
                content: String(localeData.content || '')
            };
        }
    }

    // Fallback на русскую локаль
    if (locale !== DEFAULT_LOCALE && page[DEFAULT_LOCALE] && typeof page[DEFAULT_LOCALE] === 'object') {
        const ruData = page[DEFAULT_LOCALE];
        if (ruData.title !== undefined || ruData.content !== undefined) {
            return {
                title: String(ruData.title || ''),
                content: String(ruData.content || '')
            };
        }
    }

    return null;
}

/**
 * Находит страницу по slug в store
 */
function findPageBySlug(slug: string): any {
    if (!slug || !pageStore.pages || typeof pageStore.pages !== 'object') {
        return null;
    }

    const normalizedSlug = slug.toLowerCase().trim();

    // Прямое совпадение
    if (pageStore.pages[slug] && typeof pageStore.pages[slug] === 'object') {
        return pageStore.pages[slug];
    }

    // Поиск по нормализованному slug
    for (const [key, value] of Object.entries(pageStore.pages)) {
        if (key.toLowerCase().trim() === normalizedSlug && typeof value === 'object') {
            return value;
        }
    }

    return null;
}

/**
 * Обновляет данные страницы без загрузки с сервера (для быстрого переключения языка)
 */
function updatePageDataForLocale(locale: string): boolean {
    const slug = extractSlugFromPath(route.path);
    if (!slug) return false;

    const page = findPageBySlug(slug);
    if (!page) return false;

    const extractedData = extractPageData(page, locale);
    if (!extractedData || (!extractedData.title && !extractedData.content)) {
        return false;
    }

    pageData.value = extractedData;
    applySEO(extractedData);
    return true;
}

/**
 * Загружает данные страницы
 */
async function loadPage(forceFetch = false): Promise<void> {
    isLoading.value = true;
    pageData.value = null;

    try {
        const slug = extractSlugFromPath(route.path);
        if (!slug) {
            isLoading.value = false;
            return;
        }

        const locale = currentLocale.value;

        // Проверяем, нужно ли загружать данные
        const needsFetch = forceFetch || 
                          !pageStore.pages || 
                          Object.keys(pageStore.pages).length === 0 || 
                          pageStore.currentLocale !== locale;

        if (needsFetch) {
            await pageStore.fetchData(locale, forceFetch);
        }

        // Ищем страницу по slug
        const page = findPageBySlug(slug);
        if (!page) {
            isLoading.value = false;
            return;
        }

        // Извлекаем данные для текущей локали
        const extractedData = extractPageData(page, locale);
        if (!extractedData || (!extractedData.title && !extractedData.content)) {
            isLoading.value = false;
            return;
        }

        pageData.value = extractedData;
        applySEO(extractedData);

    } catch (error) {
        console.error('[ContentPage] Error loading page:', error);
        pageData.value = null;
    } finally {
        isLoading.value = false;
    }
}

/**
 * Применяет SEO метаданные
 */
function applySEO(data: { title: string; content: string }): void {
    try {
        const description = data.content
            .replace(/<[^>]+>/g, '')
            .slice(0, 220)
            .trim();

        if (data.title || description) {
            updateWebPageSEO({
                title: data.title || 'Страница',
                description: description || '',
                canonical: route.path
            });
        }
    } catch (error) {
        console.error('[ContentPage] Error applying SEO:', error);
    }
}

// Загружаем страницу при монтировании
onMounted(() => {
    loadPage();
});

// Перезагружаем при изменении маршрута
watch(() => route.path, () => {
    loadPage();
});

// Оптимизированное переключение языка: сначала пробуем использовать уже загруженные данные
watch(currentLocale, async (newLocale, oldLocale) => {
    if (!oldLocale || newLocale === oldLocale) return;

    // Пробуем обновить данные без загрузки с сервера
    if (updatePageDataForLocale(newLocale)) {
        return; // Данные обновлены, загрузка не нужна
    }

    // Если данных нет, загружаем с сервера
    await loadPage(true);
});
</script>

<style scoped>
/* Базовые стили для контентных страниц */
.content-page {
    line-height: 1.8;
}

/* Стили для HTML контента */
.content-page :deep(p),
.content-page :deep(div),
.content-page :deep(span),
.content-page :deep(li),
.content-page :deep(td),
.content-page :deep(th) {
    color: inherit;
}

/* Исправление черного цвета текста в темной теме */
.dark .content-page :deep(p),
.dark .content-page :deep(div),
.dark .content-page :deep(span),
.dark .content-page :deep(li),
.dark .content-page :deep(td),
.dark .content-page :deep(th) {
    color: rgb(209, 213, 219); /* gray-300 */
}

/* Перекрытие черного цвета из inline стилей в темной теме */
.dark .content-page :deep([style*="color: black"]),
.dark .content-page :deep([style*="color:#000"]),
.dark .content-page :deep([style*="color:#000000"]),
.dark .content-page :deep([style*="color: rgb(0, 0, 0)"]),
.dark .content-page :deep([style*="color: rgb(0,0,0)"]),
.dark .content-page :deep([style*="color: #000"]),
.dark .content-page :deep([style*="color: #000000"]) {
    color: rgb(209, 213, 219) !important; /* gray-300 */
}

.content-page :deep(h1),
.content-page :deep(h2),
.content-page :deep(h3),
.content-page :deep(h4),
.content-page :deep(h5),
.content-page :deep(h6) {
    color: rgb(17, 24, 39); /* gray-900 */
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.dark .content-page :deep(h1),
.dark .content-page :deep(h2),
.dark .content-page :deep(h3),
.dark .content-page :deep(h4),
.dark .content-page :deep(h5),
.dark .content-page :deep(h6) {
    color: rgb(209, 213, 219); /* gray-300 */
}

/* Исправление белого цвета текста в светлой теме */
.content-page :deep([style*="color: white"]),
.content-page :deep([style*="color:#fff"]),
.content-page :deep([style*="color:#ffffff"]),
.content-page :deep([style*="color: rgb(255, 255, 255)"]),
.content-page :deep([style*="color: rgb(255,255,255)"]) {
    color: rgb(17, 24, 39) !important; /* gray-900 */
}

.dark .content-page :deep([style*="color: white"]),
.dark .content-page :deep([style*="color:#fff"]),
.dark .content-page :deep([style*="color:#ffffff"]),
.dark .content-page :deep([style*="color: rgb(255, 255, 255)"]),
.dark .content-page :deep([style*="color: rgb(255,255,255)"]) {
    color: rgb(209, 213, 219) !important; /* gray-300 */
}

/* Стили для ссылок */
.content-page :deep(a) {
    color: rgb(59, 130, 246); /* blue-500 */
    text-decoration: underline;
    transition: color 0.2s;
}

.content-page :deep(a:hover) {
    color: rgb(37, 99, 235); /* blue-600 */
}

.dark .content-page :deep(a) {
    color: rgb(96, 165, 250); /* blue-400 */
}

.dark .content-page :deep(a:hover) {
    color: rgb(147, 197, 253); /* blue-300 */
}

/* Стили для списков */
.content-page :deep(ul),
.content-page :deep(ol) {
    margin-left: 1.5rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.content-page :deep(li) {
    margin-bottom: 0.5rem;
}

/* Стили для таблиц */
.content-page :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.content-page :deep(th),
.content-page :deep(td) {
    padding: 0.5rem;
    border: 1px solid rgb(209, 213, 219);
}

.dark .content-page :deep(th),
.dark .content-page :deep(td) {
    border-color: rgb(55, 65, 81);
    color: rgb(209, 213, 219); /* gray-300 */
}

/* Стили для изображений */
.content-page :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1rem 0;
}
</style>
