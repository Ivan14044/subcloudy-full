<template>
  <router-view />
  <UpdateNotification />
</template>

<script setup lang="ts">
import { onMounted, onErrorCaptured } from 'vue';
import { useDarkMode } from './composables/useDarkMode';
import UpdateNotification from './components/UpdateNotification.vue';

const { init } = useDarkMode();

onMounted(() => {
  try {
    init();
  } catch (error: any) {
    console.error('[App] Init error:', error);
  }
});

onErrorCaptured((error: any) => {
  console.error('[App] Error captured:', error);
  
  // Игнорируем ошибки linked format - они не критичны для работы приложения
  if (error?.message?.includes('Invalid linked format') || error?.message?.includes('linked format')) {
    console.warn('[App] Ignoring linked format error, continuing...');
    return true; // Продолжаем рендеринг несмотря на ошибку
  }
  
  return false;
});
</script>

<style>
/* Глобальные стили */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body, #app {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}

/* Tailwind dark mode будет работать через класс .dark на <html> */
html.dark {
  color-scheme: dark;
}

/* Красивый скроллбар */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.dark ::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.3);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>
