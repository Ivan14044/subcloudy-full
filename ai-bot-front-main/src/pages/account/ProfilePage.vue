<template>
    <div class="w-full lg:w-1/2 mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-light text-gray-900 dark:text-white mt-3">
                {{ $t('profile.title') }}
            </h1>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
            <div class="space-y-2">
                <label class="text-sm text-gray-700 dark:text-gray-300" for="name">{{
                    $t('profile.name')
                }}</label>
                <div class="relative">
                    <User
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 w-5 h-5"
                    />
                    <input
                        id="name"
                        v-model="name"
                        type="text"
                        class="w-full pl-10 pr-4 py-2 border rounded-lg dark:!border-gray-500 dark:text-gray-300"
                        :placeholder="$t('profile.namePlaceholder')"
                        required
                    />
                </div>
                <p v-if="errors.name" class="text-red-500 text-sm">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div class="space-y-2">
                <label class="text-sm text-gray-700 dark:text-gray-300" for="email">{{
                    $t('profile.email')
                }}</label>
                <div class="relative">
                    <Mail
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 w-5 h-5"
                    />
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        class="w-full pl-10 pr-4 py-2 border dark:!border-gray-500 rounded-lg dark:text-gray-300"
                        :placeholder="$t('profile.emailPlaceholder')"
                        required
                    />
                </div>
                <p v-if="errors.email" class="text-red-500 text-sm">
                    {{ errors.email[0] }}
                </p>
            </div>

            <div class="space-y-2">
                <label class="text-sm text-gray-700 dark:text-gray-300" for="password">{{
                    $t('profile.password')
                }}</label>
                <div class="relative">
                    <Lock
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 w-5 h-5"
                    />
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        autocomplete="off"
                        class="w-full pl-10 pr-4 py-2 border dark:!border-gray-500 rounded-lg dark:text-gray-300"
                        :placeholder="$t('profile.passwordPlaceholder')"
                    />
                </div>
                <p v-if="errors.password" class="text-red-500 text-sm">
                    {{ errors.password[0] }}
                </p>
            </div>

            <div class="space-y-2">
                <label
                    class="text-sm text-gray-700 dark:text-gray-300"
                    for="password_confirmation"
                    >{{ $t('profile.confirmPassword') }}</label
                >
                <div class="relative">
                    <Lock
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 w-5 h-5"
                    />
                    <input
                        id="password_confirmation"
                        v-model="password_confirmation"
                        type="password"
                        autocomplete="off"
                        class="w-full pl-10 pr-4 py-2 border dark:!border-gray-500 rounded-lg dark:text-gray-300"
                        :placeholder="$t('profile.confirmPasswordPlaceholder')"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm text-gray-700 dark:text-gray-300">
                    {{ $t('profile.keyboard.title') }}
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $t('profile.keyboard.hint') }}
                </p>
                <div class="flex flex-wrap gap-3 pt-1">
                    <button
                        v-for="lang in allKeyboardLanguages"
                        :key="lang"
                        type="button"
                        :disabled="lang === 'en'"
                        class="relative inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 uppercase text-xs font-medium border dark:!border-gray-600 disabled:cursor-not-allowed"
                        :class="
                            isSelected(lang)
                                ? 'bg-blue-600 dark:bg-blue-700 text-white border-blue-600 dark:border-blue-700'
                                : 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-300'
                        "
                        @click="toggleLanguage(lang)"
                    >
                        <span>{{ $t('profile.keyboard.codes.' + lang) }}</span>
                        <Check v-if="isSelected(lang)" class="w-3 h-3" />
                    </button>
                </div>
            </div>
            <button
                type="submit"
                class="w-full text-white py-2 rounded-lg bg-blue-500 dark:bg-blue-900 hover:bg-blue-600 dark:hover:bg-blue-800"
            >
                {{ $t('profile.saveButton') }}
            </button>
        </form>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Lock, Mail, User, Check } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';

const toast = useToast();
const authStore = useAuthStore();
const allKeyboardLanguages = ['en', 'es', 'ru', 'uk', 'zh'] as string[];
const email = ref(authStore.user.email ?? '');
const name = ref(authStore.user.name ?? '');
const password = ref('');
const password_confirmation = ref('');
type FormErrors = Record<string, string[]>;
const errors = ref<FormErrors>({});
const { t } = useI18n();

function getInitialKeyboardLanguages(): string[] {
    // 1) Prefer languages from backend user.extension_settings.keyboardLanguages if present
    const fromUser = (authStore.user as any)?.extension_settings?.keyboardLanguages;

    // 2) Fallback to localStorage
    const saved = localStorage.getItem('sc_keyboardLanguages');
    let fromStorage: unknown = null;
    try {
        fromStorage = saved ? JSON.parse(saved) : null;
    } catch {
        fromStorage = null;
    }

    const validSet = new Set(allKeyboardLanguages);
    let selected: string[] = [];

    if (Array.isArray(fromUser)) {
        selected = (fromUser as unknown[])
            .filter(v => typeof v === 'string')
            .map(v => String(v))
            .filter(v => validSet.has(v));
    } else if (Array.isArray(fromStorage)) {
        selected = (fromStorage as unknown[])
            .filter(v => typeof v === 'string')
            .map(v => String(v))
            .filter(v => validSet.has(v));
    }

    if (!selected.includes('en')) selected.unshift('en');
    return Array.from(new Set(selected));
}

const keyboardLanguages = ref<string[]>(getInitialKeyboardLanguages());
const isSelected = (lang: string) => keyboardLanguages.value.includes(lang);
const toggleLanguage = (lang: string) => {
    if (lang === 'en') return;
    const set = new Set(keyboardLanguages.value);
    if (set.has(lang)) set.delete(lang);
    else set.add(lang);
    set.add('en');
    keyboardLanguages.value = Array.from(set);
};

// Keep keyboard languages in sync with backend user data when it loads/updates
watch(
    () => (authStore.user as any)?.extension_settings?.keyboardLanguages,
    (langs) => {
        const validSet = new Set(allKeyboardLanguages);
        let selected: string[] = Array.isArray(langs)
            ? (langs as unknown[])
                  .filter(v => typeof v === 'string')
                  .map(v => String(v))
                  .filter(v => validSet.has(v))
            : [];
        if (!selected.includes('en')) selected.unshift('en');
        keyboardLanguages.value = Array.from(new Set(selected));
    },
    { immediate: true }
);

const handleSubmit = async () => {
    const langs = Array.from(new Set([...(keyboardLanguages.value || []), 'en']));
    const payload: any = {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
        extension_settings: {
            ...((authStore.user as any)?.extension_settings ?? {}),
            keyboardLanguages: langs
        }
    };

    // Persist locally for now in case backend does not accept the field yet
    try {
        localStorage.setItem('sc_keyboardLanguages', JSON.stringify(langs));
    } catch {
        /* empty */
    }

    const success = await authStore.update(payload);
    errors.value = authStore.errors;

    if (success) {
        toast.success(t('profile.success'));
        password.value = '';
        password_confirmation.value = '';
    }
};
</script>
