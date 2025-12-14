<template>
  <button
    @click="toggleTheme"
    class="theme-toggle px-2 px-lg-3 py-2 h-[32px] rounded-lg hover:bg-indigo-200 dark:hover:bg-gray-700 transition-all duration-300 flex items-center gap-2"
    :title="isDark ? 'Светлая тема' : 'Тёмная тема'"
  >
    <!-- Sun icon (light mode) -->
    <svg
      v-if="!isDark"
      class="w-5 h-5 text-gray-900"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
      />
    </svg>

    <!-- Moon icon (dark mode) -->
    <svg
      v-else
      class="w-5 h-5 text-white"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
      />
    </svg>
  </button>
</template>

<script setup lang="ts">
import { useDarkMode } from '../composables/useDarkMode';

const { isDark, toggle } = useDarkMode();

function toggleTheme() {
  toggle();
}
</script>

<style scoped>
.theme-toggle {
  cursor: pointer;
  user-select: none;
}

.theme-toggle:active {
  transform: scale(0.95);
}
</style>


