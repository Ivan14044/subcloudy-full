<template>
    <div v-if="pageStore.page && currentPageData" class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-16">
            <h1 class="text-4xl font-light text-gray-900 dark:text-white mt-3">
                {{ currentPageData.title }}
            </h1>
        </div>

        <div class="content-page text-gray-900 dark:text-gray-300" v-html="currentPageData.content"></div>
    </div>
</template>

<script setup lang="ts">
import { usePageStore } from '@/stores/pages';
import { useI18n } from 'vue-i18n';
import { onMounted, watch, computed } from 'vue';
import { updateWebPageSEO } from '@/utils/seo';

const pageStore = usePageStore();
const { locale } = useI18n();

// Получаем данные для текущей локали
const currentPageData = computed(() => {
    if (!pageStore.page) return null;
    const currentLocale = locale.value;
    // Пробуем получить данные для текущей локали, если нет - используем ru как fallback
    return pageStore.page[currentLocale] || pageStore.page['ru'] || null;
});

function applySeo() {
    try {
        const data = currentPageData.value;
        if (!data) return;
        updateWebPageSEO({
            title: data.title,
            description: (data.content || '').replace(/<[^>]+>/g, '').slice(0, 220),
            canonical: window.location.pathname
        });
    } catch {}
}

onMounted(() => {
    // Перезагружаем данные при смене локали
    pageStore.fetchData(locale.value);
    applySeo();
});

watch(() => locale.value, (newLocale) => {
    // При смене локали перезагружаем данные
    pageStore.fetchData(newLocale);
    applySeo();
});

watch(() => pageStore.page, () => {
    applySeo();
});
</script>

<style scoped>
/* Стили для контентных страниц */
.content-page {
    line-height: 1.8;
}

/* Переопределяем белый цвет текста в HTML контенте для светлой темы */
.content-page :deep(p),
.content-page :deep(div),
.content-page :deep(span),
.content-page :deep(li),
.content-page :deep(td),
.content-page :deep(th) {
    color: inherit;
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

/* Убираем белый цвет из встроенных стилей на светлой теме */
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
</style>
