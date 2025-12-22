<template>
    <ul class="footer-menu-list">
        <li
            v-for="(item, index) in footerMenu"
            :key="index"
            class="footer-menu-item"
            @click="handleClick(item)"
        >
            <span class="footer-menu-link">{{ item.title }}</span>
        </li>
    </ul>
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
    if (!str || str.trim() === '') {
        return fallback;
    }
    try {
        const parsed = JSON.parse(str);
        return parsed as T;
    } catch (e) {
        console.warn('[FooterMenu] JSON parse error:', e, 'String:', str.substring(0, 100));
        return fallback;
    }
}

function handleClick(item: { link: string; is_target?: boolean; is_blank?: boolean }) {
    if (!item || !item.link) {
        console.warn('[FooterMenu] Invalid item or link:', item);
        return;
    }
    
    const isTarget = item.is_target || item.is_blank;
    
    if (isTarget) {
        window.open(item.link, '_blank', 'noopener,noreferrer');
    } else {
        let link = item.link;
        if (!link.startsWith('/') && !link.startsWith('http://') && !link.startsWith('https://')) {
            link = `/${link}`;
        }
        
        router.push(link).catch(err => {
            if (err.name !== 'NavigationDuplicated') {
                window.location.href = link;
            }
        });
    }
}

const footerMenu = computed(() => {
    if (!serviceOption.isLoaded) {
        return [];
    }
    
    let options = serviceOption.options;
    
    if (options && typeof options === 'object') {
        try {
            const testStringify = JSON.stringify(options);
            options = JSON.parse(testStringify);
        } catch (e) {
            if (Array.isArray(options)) {
                options = [...options];
            } else {
                options = { ...options };
            }
        }
    }
    
    let optionsObj: Record<string, any>;
    if (Array.isArray(options)) {
        optionsObj = {};
        options.forEach(option => {
            if (option && option.key) {
                optionsObj[option.key] = option.value;
            }
        });
    } else if (typeof options === 'object' && options !== null) {
        optionsObj = { ...options };
    } else {
        return [];
    }
    
    const raw = optionsObj.footer_menu;
    
    if (!raw) {
        return [];
    }
    
    const menusByLocale = parseJson<Record<string, string>>(raw, {});
    const menuStr = menusByLocale[locale.value] ?? menusByLocale['ru'] ?? '[]';
    const menuItems = parseJson<Array<{ title: string; link: string; is_target?: boolean; is_blank?: boolean }>>(menuStr, []);
    
    // Фильтруем дубликаты
    const legalLinks = ['/terms-of-service', '/privacy-policy', '/refund-policy', 'terms-of-service', 'privacy-policy', 'refund-policy'];
    const filteredItems = menuItems.filter(item => {
        const link = item.link?.toLowerCase().replace(/^\//, '') || '';
        return !legalLinks.some(legal => link.includes(legal));
    });
    
    return filteredItems;
});
</script>

<style scoped>
.footer-menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-menu-item {
    cursor: pointer;
}

.footer-menu-link {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    display: inline-block;
    padding-left: 0;
}

.footer-menu-link::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 0;
    height: 1px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    transition: width 0.3s ease;
}

.footer-menu-item:hover .footer-menu-link {
    color: #ffffff;
    padding-left: 8px;
}

.footer-menu-item:hover .footer-menu-link::before {
    width: 4px;
}
</style>
