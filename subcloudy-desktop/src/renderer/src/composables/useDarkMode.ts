import { ref, watch, onMounted } from 'vue';

const isDark = ref(true); // По умолчанию темная тема

export function useDarkMode() {
  const toggle = () => {
    isDark.value = !isDark.value;
    updateDOM();
    savePreference();
  };

  const setDark = (value: boolean) => {
    isDark.value = value;
    updateDOM();
    savePreference();
  };

  const updateDOM = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const savePreference = () => {
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  };

  const loadPreference = () => {
    const saved = localStorage.getItem('theme');
    if (saved) {
      isDark.value = saved === 'dark';
    } else {
      // Проверка системных настроек
      if (window.matchMedia) {
        const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        isDark.value = darkModeQuery.matches;
      }
    }
    updateDOM();
  };

  const init = () => {
    try {
      loadPreference();

      // Следим за изменениями системной темы
      if (window.matchMedia) {
        const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
        darkModeQuery.addEventListener('change', (e) => {
          const saved = localStorage.getItem('theme');
          if (!saved) {
            // Только если пользователь не установил тему вручную
            isDark.value = e.matches;
            updateDOM();
          }
        });
      }
    } catch (error: any) {
      console.error('[useDarkMode] Init error:', error);
    }
  };

  return {
    isDark,
    toggle,
    setDark,
    init
  };
}


