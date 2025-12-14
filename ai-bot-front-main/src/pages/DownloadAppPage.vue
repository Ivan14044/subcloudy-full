<template>
    <div class="w-full lg:w-1/2 mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="mb-8">
                <svg
                    class="w-24 h-24 mx-auto text-blue-500 dark:text-blue-400 mb-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>
            <h1 class="text-4xl font-light text-gray-900 dark:text-white mt-3 mb-6">
                {{ $t('downloadApp.title') }}
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg leading-relaxed">
                {{ $t('downloadApp.description') }}
            </p>
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-lg p-8 mb-6 border border-gray-200 dark:border-gray-700"
            >
                <p class="text-gray-700 dark:text-gray-300 mb-6 text-base leading-relaxed">
                    {{ $t('downloadApp.info') }}
                </p>
                <div class="flex flex-col gap-4 justify-center items-center">
                    <!-- Кнопки для скачивания -->
                    <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md">
                        <!-- Windows кнопка -->
                        <a
                            :href="windowsDownloadUrl"
                            :class="[
                                'flex-1 px-6 py-4 rounded-lg font-medium transition-all text-center',
                                os === 'windows'
                                    ? 'bg-blue-500 dark:bg-blue-900 hover:bg-blue-600 dark:hover:bg-blue-800 text-white text-lg shadow-lg transform scale-105'
                                    : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200'
                            ]"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-13.051-1.35"/>
                                </svg>
                                <span>{{ $t('downloadApp.windows') }}</span>
                            </div>
                        </a>

                        <!-- macOS кнопка -->
                        <a
                            :href="macOSDownloadUrl"
                            :class="[
                                'flex-1 px-6 py-4 rounded-lg font-medium transition-all text-center',
                                os === 'macos'
                                    ? 'bg-blue-500 dark:bg-blue-900 hover:bg-blue-600 dark:hover:bg-blue-800 text-white text-lg shadow-lg transform scale-105'
                                    : 'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200'
                            ]"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                                </svg>
                                <span>{{ $t('downloadApp.macos') }}</span>
                            </div>
                        </a>
                    </div>

                    <!-- Кнопка "Назад" -->
                    <button
                        @click="$router.back()"
                        class="w-full max-w-md bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-8 py-3 rounded-lg font-medium transition-colors"
                    >
                        {{ $t('downloadApp.back') }}
                    </button>
                </div>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <p>{{ $t('downloadApp.note') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useOSDetection } from '@/composables/useOSDetection';

const router = useRouter();
const { os } = useOSDetection();

// URL для скачивания (замените на реальные ссылки)
const windowsDownloadUrl = computed(() => '/download/windows');
const macOSDownloadUrl = computed(() => '/download/macos');
</script>

