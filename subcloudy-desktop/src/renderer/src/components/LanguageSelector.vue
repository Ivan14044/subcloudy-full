<template>
    <div ref="dropdownRef" class="z-50 relative">
        <!-- Language Button -->
        <button
            ref="buttonRef"
            class="cursor-pointer gap-[7px] text-sm leading-4 hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 pl-2 pr-1 pl-lg-3 pr-lg-2 py-2 rounded-lg flex items-center"
            @click="toggleDropdown"
        >
            <img 
                :src="getFlagPath(currentLanguage.code)" 
                style="width: 24px; height: 16px"
            />
            <span class="d-flex">
                <span class="hidden xl:flex text-[15px] text-gray-900 dark:text-white">{{ currentLanguage.name }}</span>
                <svg 
                    :class="[
                        'xl:ml-1 w-4 h-4 transition-transform duration-300 text-gray-900 dark:text-white',
                        isOpen ? 'rotate-180' : 'rotate-0'
                    ]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="isOpen"
                style="will-change: transform; transform: translateZ(0)"
                class="absolute top-[45px] right-0 bg-indigo-soft-200/90 dark:bg-gray-800/90 rounded-lg border !border-indigo-soft-400 dark:!border-gray-700 overflow-hidden min-w-[160px]"
            >
                <div>
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        class="flex h-[44px] items-center gap-3 w-full px-4 text-sm text-left hover:bg-indigo-200 dark:hover:bg-gray-700 transition-colors relative"
                        :class="[
                            language.code === currentLocale
                                ? 'text-gray-800 dark:text-blue-700 font-weight-bold'
                                : 'text-gray-900 dark:!text-white'
                        ]"
                        @click="changeLanguage(language.code)"
                    >
                        <span class="relative z-10 flex items-center">
                            <span class="pr-2 flex items-center">
                                <img
                                    :src="getFlagPath(language.code)"
                                    class="w-6 h-4 object-cover transition-opacity duration-300 align-middle inline-block"
                                />
                            </span>
                            {{ language.name }}
                        </span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const languages = [
    { code: 'en', name: 'English' },
    { code: 'uk', name: 'Українська' },
    { code: 'ru', name: 'Русский' }
];

const { locale } = useI18n();
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const currentLocale = computed(() => locale.value);

const currentLanguage = computed(
    () => languages.find(lang => lang.code === currentLocale.value) || languages[0]
);

// Функция для получения правильного пути к флагу
const getFlagPath = (code: string): string => {
    // В Electron при использовании file:// протокола нужно использовать относительный путь
    if (window.location.protocol === 'file:') {
        return `./img/lang/${code}.png`;
    }
    // В dev режиме (http://) используем абсолютный путь
    return `/img/lang/${code}.png`;
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const changeLanguage = (code: string) => {
    locale.value = code;
    localStorage.setItem('user-language', code);
    isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    const savedLanguage = localStorage.getItem('user-language');
    if (savedLanguage) {
        locale.value = savedLanguage;
    }

    document.addEventListener('mousedown', handleClickOutside);

    // Предзагрузка изображений флагов
    languages.forEach(lang => {
        const img = new Image();
        img.src = getFlagPath(lang.code);
    });
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<style scoped>
.indigo-soft-200\/90 {
    background-color: rgba(224, 231, 255, 0.9);
}

.indigo-soft-400 {
    border-color: rgba(165, 180, 252, 1);
}

.dark .indigo-soft-200\/90 {
    background-color: rgba(31, 41, 55, 0.9);
}

.dark .indigo-soft-400 {
    border-color: rgba(75, 85, 99, 1);
}

.font-weight-bold {
    font-weight: 700;
}
</style>
