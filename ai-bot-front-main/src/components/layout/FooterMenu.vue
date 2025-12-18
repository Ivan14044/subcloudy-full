<template>
    <div>
        <ul class="justify-end mt-3 mb-3 md:mb-7 flex flex-col gap-5 md:flex-row">
            <li
                v-for="(item, index) in footerMenu"
                :key="index"
                class="cursor-pointer font-medium text-gray-300 hover:text-white transition-colors duration-300"
                @click="handleClick(item)"
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

function handleClick(item: { link: string; is_target: boolean }) {
    console.log('[FooterMenu] handleClick called with item:', item);
    console.log('[FooterMenu] Item type:', typeof item);
    console.log('[FooterMenu] Item link:', item?.link);
    console.log('[FooterMenu] Item is_target:', item?.is_target);
    
    if (!item || !item.link) {
        console.warn('[FooterMenu] Invalid item or link', item);
        return;
    }
    
    if (item.is_target) {
        console.log('[FooterMenu] Opening link in new tab:', item.link);
        window.open(item.link, '_blank');
    } else {
        // ????????????????????, ?????? ???????????? ???????????????????? ?? /
        const link = item.link.startsWith('/') ? item.link : `/${item.link}`;
        console.log('[FooterMenu] Navigating to:', link);
        router.push(link).then(() => {
            console.log('[FooterMenu] Navigation successful to:', link);
        }).catch(err => {
            console.error('[FooterMenu] Navigation error:', err);
            console.error('[FooterMenu] Error details:', err.message, err.stack);
        });
    }
}

const footerMenu = computed(() => {
    console.log('[FooterMenu] Computing footer menu...');
    const raw = serviceOption.options.footer_menu;
    console.log('[FooterMenu] Raw footer_menu:', raw);
    console.log('[FooterMenu] Current locale:', locale.value);
    
    if (!raw) {
        console.warn('[FooterMenu] No footer_menu in options');
        return [];
    }
    
    try {
        const parsed = JSON.parse(raw);
        console.log('[FooterMenu] Parsed footer_menu:', parsed);
        const localeMenu = parsed[locale.value];
        console.log('[FooterMenu] Menu for locale:', localeMenu);
        
        if (!localeMenu) {
            console.warn('[FooterMenu] No menu for locale:', locale.value);
            return [];
        }
        
        const menu = JSON.parse(localeMenu);
        console.log('[FooterMenu] Final menu items:', menu);
        return menu;
    } catch (error) {
        console.error('[FooterMenu] Error parsing footer_menu:', error);
        console.error('[FooterMenu] Error stack:', error.stack);
        return [];
    }
});
</script>
