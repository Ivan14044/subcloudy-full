<template>
    <div style="position: relative; z-index: 10;">
        <ul class="justify-end mt-3 mb-3 md:mb-7 flex flex-col gap-5 md:flex-row" style="position: relative; z-index: 10;">
            <li
                v-for="(item, index) in footerMenu"
                :key="index"
                class="cursor-pointer font-medium text-gray-300 hover:text-white transition-colors duration-300"
                @click.stop.prevent="handleClick(item)"
                @mousedown.stop="console.log('[FooterMenu] Mouse down on item:', item)"
                style="pointer-events: auto !important; user-select: none; position: relative; z-index: 11;"
            >
                {{ item.title }}
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useOptionStore } from '@/stores/options';
import { useI18n } from 'vue-i18n';

const serviceOption = useOptionStore();
const router = useRouter();
const { locale } = useI18n();

// Безопасная функция для парсинга JSON
function parseJson<T>(str: string | T, fallback: T): T {
    if (typeof str !== 'string') {
        return str as T;
    }
    try {
        return JSON.parse(str) as T;
    } catch {
        return fallback;
    }
}

function handleClick(item: { link: string; is_target: boolean }) {
    if (!item || !item.link) {
        return;
    }
    
    if (item.is_target) {
        window.open(item.link, '_blank');
    } else {
        // Убеждаемся, что ссылка начинается с /
        const link = item.link.startsWith('/') ? item.link : `/${item.link}`;
        router.push(link).catch(err => {
            console.error('[FooterMenu] Navigation error:', err);
        });
    }
}

const footerMenu = computed(() => {
    // Получаем options - может быть массив или объект
    const options = serviceOption.options;
    
    // Если options - массив, преобразуем в объект
    let optionsObj: Record<string, any>;
    if (Array.isArray(options)) {
        optionsObj = {};
        options.forEach(option => {
            if (option && option.key) {
                optionsObj[option.key] = option.value;
            }
        });
    } else if (typeof options === 'object' && options !== null) {
        optionsObj = options;
    } else {
        return [];
    }
    
    const raw = optionsObj.footer_menu;
    if (!raw) {
        return [];
    }
    
    // Парсим footer_menu (может быть строкой JSON или объектом)
    const menusByLocale = parseJson<Record<string, string>>(raw, {});
    const menuStr = menusByLocale[locale.value] ?? '[]';
    
    // Парсим меню для текущей локали
    return parseJson<Array<{ title: string; link: string; is_target: boolean }>>(menuStr, []);
});
</script>
