<template>
    <div class="relative flex items-center gap-5">
        <ul class="h-full items-center hidden lg:flex gap-1.5 flex-nowrap overflow-hidden" style="position: relative; z-index: 20;">
            <li
                v-for="(item, index) in allMenuItems"
                :key="index"
                class="flex items-center"
            >
                <a
                    v-if="item.is_scroll"
                    :href="item.link"
                    class="cursor-pointer text-base !text-[14px] h-[30px] flex items-center leading-[15px] hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 px-1.5 lg:px-2.5 py-2 rounded-lg whitespace-nowrap flex-shrink-0"
                    @click.stop.prevent="handleClick(item)"
                    style="position: relative; z-index: 21; pointer-events: auto !important;"
                >
                    {{ item.title }}
                </a>
                <a
                    v-else-if="item.is_blank"
                    :href="item.link"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="cursor-pointer text-base !text-[14px] h-[30px] flex items-center leading-[15px] hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 px-1.5 lg:px-2.5 py-2 rounded-lg whitespace-nowrap flex-shrink-0"
                    @click.stop="isMobileMenuOpen = false"
                    style="position: relative; z-index: 21; pointer-events: auto !important;"
                >
                    {{ item.title }}
                </a>
                <router-link
                    v-else
                    :to="item.link"
                    class="cursor-pointer text-base !text-[14px] h-[30px] flex items-center leading-[15px] hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 px-1.5 lg:px-2.5 py-2 rounded-lg whitespace-nowrap flex-shrink-0"
                    @click.stop="isMobileMenuOpen = false"
                    style="position: relative; z-index: 21; pointer-events: auto !important;"
                >
                    {{ item.title }}
                </router-link>
            </li>
        </ul>

        <!-- Кнопка бургер для мобильной версии -->
        <button
            v-if="allMenuItems.length > 0"
            class="flex items-center lg:hidden text-gray-700 hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors !text-[15px] h-[30px] leading-[15px] duration-300 px-2 lg:px-3 py-2 rounded-lg"
            :class="{ 'mr-[-20px]': isMobileMenuOpen }"
            @click="isMobileMenuOpen = true"
            :aria-label="$t('menu.openMenu')"
            :aria-expanded="isMobileMenuOpen"
        >
            <Menu class="w-5 h-5 text-gray-700 dark:text-white" />
        </button>

        <!-- Мобильное меню -->
        <MobileMenu
            v-if="isMobileMenuOpen"
            :is-open="isMobileMenuOpen"
            :menu-items="allMenuItems"
            @close="isMobileMenuOpen = false"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useOptionStore } from '../stores/options';
import { useI18n } from 'vue-i18n';
import MobileMenu from './MobileMenu.vue';
import { Menu } from 'lucide-vue-next';
import { scrollToElement } from '@/utils/scrollToElement';

const serviceOption = useOptionStore();
const router = useRouter();
const route = useRoute();
const { locale, t } = useI18n();
const isMobileMenuOpen = ref(false);

interface MenuItem {
    title: string;
    link: string;
    is_blank?: boolean;
    is_scroll?: boolean; // Для скролла к секциям на главной странице
}

function parseJson<T>(str: string, fallback: T): T {
    if (!str || typeof str !== 'string') {
        return fallback;
    }
    try {
        const parsed = JSON.parse(str);
        return parsed as T;
    } catch (e) {
        console.warn('[MainMenu] JSON parse error:', e, 'String:', str.substring(0, 100));
        return fallback;
    }
}

// Фиксированные пункты меню
const fixedMenuItems = computed<MenuItem[]>(() => [
    {
        title: t('menu.services'),
        link: '#services',
        is_scroll: true
    },
    {
        title: t('menu.howItWorks'),
        link: '#steps',
        is_scroll: true
    },
    {
        title: t('menu.reviews'),
        link: '#reviews',
        is_scroll: true
    },
    {
        title: t('menu.faq'),
        link: '#faq',
        is_scroll: true
    },
    {
        title: t('menu.blog'),
        link: '/articles',
        is_scroll: false
    }
]);

// Динамические пункты меню из опций
const dynamicMenuItems = computed<MenuItem[]>(() => {
    // Убеждаемся, что данные загружены
    if (!serviceOption.isLoaded) {
        return [];
    }
    
    const options = serviceOption.options;
    let optionsObj: Record<string, any>;
    
    // Если options - массив, преобразуем в объект
    if (Array.isArray(options)) {
        optionsObj = {};
        // Безопасный forEach - Array.isArray уже проверил, что это массив
        try {
            options.forEach(option => {
                if (option && option.key) {
                    optionsObj[option.key] = option.value;
                }
            });
        } catch (e) {
            console.error('[MainMenu] Error in forEach:', e);
            return [];
        }
    } else if (typeof options === 'object' && options !== null && !Array.isArray(options)) {
        optionsObj = options;
    } else {
        return [];
    }
    
    const raw = optionsObj.header_menu;
    if (!raw) return [];

    const menusByLocale = parseJson<Record<string, string>>(raw, {});
    const menuStr = menusByLocale[locale.value] ?? menusByLocale['ru'] ?? '[]';

    return parseJson<MenuItem[]>(menuStr, []);
});

// Объединенные пункты меню
const allMenuItems = computed<MenuItem[]>(() => {
    return [...fixedMenuItems.value, ...dynamicMenuItems.value];
});

function handleClick(item: MenuItem) {
    console.log('[MainMenu] handleClick called with item:', item);
    
    if (!item || !item.link) {
        console.warn('[MainMenu] Invalid item or link:', item);
        return;
    }
    
    if (item.is_blank) {
        console.log('[MainMenu] Opening link in new tab:', item.link);
        window.open(item.link, '_blank', 'noopener,noreferrer');
        isMobileMenuOpen.value = false;
        return;
    }

    // Если это скролл на главной странице
    if (item.is_scroll && route.path === '/') {
        console.log('[MainMenu] Scrolling to element on home page:', item.link);
        scrollToElement(item.link);
        isMobileMenuOpen.value = false;
        return;
    }

    // Если это скролл, но мы не на главной странице
    if (item.is_scroll && route.path !== '/') {
        console.log('[MainMenu] Navigating to home then scrolling to:', item.link);
        router.push('/').then(() => {
            // Небольшая задержка, чтобы страница успела загрузиться
            setTimeout(() => {
                scrollToElement(item.link);
            }, 300);
        }).catch(err => {
            console.error('[MainMenu] Navigation error:', err);
        });
        isMobileMenuOpen.value = false;
        return;
    }

    // Обычная навигация
    console.log('[MainMenu] Navigating to:', item.link);
    router.push(item.link).then(() => {
        console.log('[MainMenu] Navigation successful to:', item.link);
    }).catch(err => {
        console.error('[MainMenu] Navigation error:', err);
        // Если навигация не удалась, пробуем открыть как обычную ссылку
        if (err.name !== 'NavigationDuplicated') {
            window.location.href = item.link;
        }
    });
    isMobileMenuOpen.value = false;
}
</script>
