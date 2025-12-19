<<<<<<< HEAD
<template>
    <div class="relative flex items-center gap-5">
        <ul class="h-100 items-center hidden lg:flex gap-2 flex-nowrap overflow-hidden">
            <li
                v-for="(item, index) in allMenuItems"
                :key="index"
                class="cursor-pointer text-base !text-[15px] h-[30px] d-flex align-center lh-[15px] hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 px-2 px-lg-3 py-2 rounded-lg whitespace-nowrap flex-shrink-0"
                @click="handleClick(item)"
            >
                {{ item.title }}
            </li>
        </ul>

        <!-- Кнопка бургер для мобильной версии -->
        <button
            v-if="allMenuItems.length > 0"
            class="flex lg:hidden text-gray-700 hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors !text-[15px] h-[30px] align-center lh-[15px] duration-300 px-2 px-lg-3 py-2 rounded-lg"
            :class="{ 'mr-[-20px]': isMobileMenuOpen }"
            @click="isMobileMenuOpen = true"
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
    try {
        return JSON.parse(str) as T;
    } catch {
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
    const raw = serviceOption.options.header_menu;
    if (!raw) return [];

    const menusByLocale = parseJson<Record<string, string>>(raw, {});
    const menuStr = menusByLocale[locale.value] ?? '[]';

    return parseJson<MenuItem[]>(menuStr, []);
});

// Объединенные пункты меню
const allMenuItems = computed<MenuItem[]>(() => {
    return [...fixedMenuItems.value, ...dynamicMenuItems.value];
});

function handleClick(item: MenuItem) {
    if (item.is_blank) {
        window.open(item.link, '_blank');
        return;
    }

    // Если это скролл на главной странице
    if (item.is_scroll && route.path === '/') {
        scrollToElement(item.link);
        isMobileMenuOpen.value = false;
        return;
    }

    // Если это скролл, но мы не на главной странице
    if (item.is_scroll && route.path !== '/') {
        router.push('/').then(() => {
            // Небольшая задержка, чтобы страница успела загрузиться
            setTimeout(() => {
                scrollToElement(item.link);
            }, 100);
        });
        isMobileMenuOpen.value = false;
        return;
    }

    // Обычная навигация
    router.push(item.link);
    isMobileMenuOpen.value = false;
}
</script>
=======
<template>
    <div class="relative flex items-center gap-5">
        <ul class="h-100 items-center hidden lg:flex gap-2 flex-nowrap overflow-hidden">
            <li
                v-for="(item, index) in allMenuItems"
                :key="index"
                class="cursor-pointer text-base !text-[15px] h-[30px] d-flex align-center lh-[15px] hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 px-2 px-lg-3 py-2 rounded-lg whitespace-nowrap flex-shrink-0"
                @click="handleClick(item)"
            >
                {{ item.title }}
            </li>
        </ul>

        <!-- Кнопка бургер для мобильной версии -->
        <button
            v-if="allMenuItems.length > 0"
            class="flex lg:hidden text-gray-700 hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors !text-[15px] h-[30px] align-center lh-[15px] duration-300 px-2 px-lg-3 py-2 rounded-lg"
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
    try {
        return JSON.parse(str) as T;
    } catch {
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
    const raw = serviceOption.options.header_menu;
    if (!raw) return [];

    const menusByLocale = parseJson<Record<string, string>>(raw, {});
    const menuStr = menusByLocale[locale.value] ?? '[]';

    return parseJson<MenuItem[]>(menuStr, []);
});

// Объединенные пункты меню
const allMenuItems = computed<MenuItem[]>(() => {
    return [...fixedMenuItems.value, ...dynamicMenuItems.value];
});

function handleClick(item: MenuItem) {
    if (item.is_blank) {
        window.open(item.link, '_blank');
        return;
    }

    // Если это скролл на главной странице
    if (item.is_scroll && route.path === '/') {
        scrollToElement(item.link);
        isMobileMenuOpen.value = false;
        return;
    }

    // Если это скролл, но мы не на главной странице
    if (item.is_scroll && route.path !== '/') {
        router.push('/').then(() => {
            // Небольшая задержка, чтобы страница успела загрузиться
            setTimeout(() => {
                scrollToElement(item.link);
            }, 100);
        });
        isMobileMenuOpen.value = false;
        return;
    }

    // Обычная навигация
    router.push(item.link);
    isMobileMenuOpen.value = false;
}
</script>
>>>>>>> dc5751d53a6f5de3299308b7b9cfe3d007191e25
