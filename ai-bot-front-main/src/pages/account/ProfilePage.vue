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
                        class="w-full pl-10 pr-4 py-2 border rounded-lg bg-white text-gray-900 dark:!bg-gray-700 dark:!border-gray-500 dark:!text-gray-300"
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
                        class="w-full pl-10 pr-4 py-2 border rounded-lg bg-white text-gray-900 dark:!bg-gray-700 dark:!border-gray-500 dark:!text-gray-300"
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
                        class="w-full pl-10 pr-4 py-2 border rounded-lg bg-white text-gray-900 dark:!bg-gray-700 dark:!border-gray-500 dark:!text-gray-300"
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
                        class="w-full pl-10 pr-4 py-2 border rounded-lg bg-white text-gray-900 dark:!bg-gray-700 dark:!border-gray-500 dark:!text-gray-300"
                        :placeholder="$t('profile.confirmPasswordPlaceholder')"
                    />
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
import { ref } from 'vue';
import { Lock, Mail, User } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth';
import { useToast } from 'vue-toastification';
import { useI18n } from 'vue-i18n';

const toast = useToast();
const authStore = useAuthStore();
const email = ref(authStore.user.email ?? '');
const name = ref(authStore.user.name ?? '');
const password = ref('');
const password_confirmation = ref('');
type FormErrors = Record<string, string[]>;
const errors = ref<FormErrors>({});
const { t } = useI18n();

const handleSubmit = async () => {
    const payload: any = {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value
    };

    const success = await authStore.update(payload);
    errors.value = authStore.errors;

    if (success) {
        toast.success(t('profile.success'));
        password.value = '';
        password_confirmation.value = '';
    }
};
</script>
