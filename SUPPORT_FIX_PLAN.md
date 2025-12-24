# План исправления багов функционала техподдержки

## КРИТИЧЕСКИЙ БАГ: Модальное окно закрывается при клике на кнопки каналов

### Проблема
При клике на кнопки "Чат на сайте" или "Telegram" модальное окно закрывается вместо перехода к следующему шагу (email или чат).

### Причина
Событие клика всплывает до overlay, и `handleOverlayClick` закрывает модальное окно.

### Решение

#### 1. Улучшить обработку кликов на кнопках каналов
**Файл:** `ai-bot-front-main/src/components/SupportModal.vue`

**Изменения:**
```vue
<!-- Строка 19 и 29 -->
<button 
    type="button" 
    class="channel-card" 
    @click.stop.prevent="handleChannelClick('web', $event)"
>
```

**Добавить в `handleChannelClick`:**
```typescript
const handleChannelClick = async (channel: 'web' | 'telegram', event?: Event) => {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
        event.stopImmediatePropagation(); // Добавить это
    }
    
    // Убедиться, что модальное окно открыто
    if (!isVisible.value) {
        isVisible.value = true;
    }
    
    selectedChannel.value = channel;
    
    // Если пользователь не авторизован, запрашиваем email
    if (!authStore.isAuthenticated) {
        step.value = 'email';
        await nextTick();
        // Дополнительная проверка
        if (!isVisible.value) {
            isVisible.value = true;
        }
        emailInputRef.value?.focus();
    } else {
        // Для авторизованных пользователей сразу запускаем чат
        await initializeChat();
    }
};
```

#### 2. Улучшить `handleOverlayClick`
**Файл:** `ai-bot-front-main/src/components/SupportModal.vue`

**Изменения:**
```typescript
const handleOverlayClick = (event: MouseEvent) => {
    // Закрываем только на шаге чата и если клик был непосредственно по оверлею
    // НЕ закрываем, если клик был по дочерним элементам
    const target = event.target as HTMLElement;
    const currentTarget = event.currentTarget as HTMLElement;
    
    if (step.value !== 'chat') {
        return; // Не закрываем на других шагах
    }
    
    // Проверяем, что клик был именно по overlay, а не по его дочерним элементам
    if (target === currentTarget) {
        handleClose();
    }
};
```

#### 3. Улучшить `handleClose`
**Файл:** `ai-bot-front-main/src/components/SupportModal.vue`

**Изменения:**
```typescript
const handleClose = () => {
    // СТРОГАЯ проверка: не закрываем на шагах выбора канала или ввода email
    if (step.value === 'channel' || step.value === 'email') {
        console.warn('Attempted to close modal on channel/email step - prevented');
        return;
    }
    
    isVisible.value = false;
    step.value = 'channel';
    selectedChannel.value = null;
    email.value = '';
    messageText.value = '';
    supportStore.reset();
};
```

#### 4. Добавить CSS для предотвращения закрытия
**Файл:** `ai-bot-front-main/src/components/SupportModal.vue`

**Добавить в стили:**
```css
.channel-card {
    /* ... существующие стили ... */
    pointer-events: auto !important;
    position: relative;
    z-index: 10;
}

.modal-overlay {
    /* ... существующие стили ... */
}

/* Предотвращаем закрытие при клике на контент модального окна */
.modal-container {
    pointer-events: auto;
}

.modal-overlay > * {
    pointer-events: auto;
}
```

## План выполнения

### Шаг 1: Исправить обработку кликов (КРИТИЧНО)
1. Добавить `@click.stop.prevent` на кнопки каналов
2. Добавить `event.stopImmediatePropagation()` в `handleChannelClick`
3. Улучшить `handleOverlayClick` с проверкой target === currentTarget
4. Улучшить `handleClose` с строгой проверкой шага

### Шаг 2: Улучшить валидацию email
1. Заменить простую валидацию на регулярное выражение
2. Использовать более строгую валидацию

### Шаг 3: Улучшить обработку ошибок
1. Проверить все хардкоженные сообщения в `support.ts`
2. Убедиться, что `translateError` обрабатывает все случаи

### Шаг 4: Добавить индикацию загрузки
1. Добавить спиннер при создании тикета
2. Показывать состояние загрузки на кнопках

### Шаг 5: Тестирование
1. Протестировать на всех языках
2. Протестировать для авторизованных и неавторизованных пользователей
3. Протестировать все сценарии использования

## Приоритеты

1. **СРОЧНО (Приоритет 1):** Исправить закрытие модального окна при клике на кнопки каналов
2. **ВАЖНО (Приоритет 2):** Улучшить валидацию email
3. **ВАЖНО (Приоритет 2):** Улучшить обработку ошибок
4. **УЛУЧШЕНИЕ (Приоритет 3):** Добавить индикацию загрузки
5. **УЛУЧШЕНИЕ (Приоритет 3):** Улучшить UI/UX

## Файлы для изменения

1. `ai-bot-front-main/src/components/SupportModal.vue` - основной файл (строки 4, 19, 29, 174-190, 240-248, стили)
2. `ai-bot-front-main/src/stores/support.ts` - проверка хардкоженных сообщений (строки 55, 64, 74, 89, 103)

## Ожидаемый результат

После исправлений:
- ✅ Модальное окно НЕ закрывается при клике на кнопки каналов
- ✅ Плавный переход на шаг email для неавторизованных пользователей
- ✅ Плавный переход на шаг чата для авторизованных пользователей
- ✅ Модальное окно закрывается только при клике на overlay на шаге чата
- ✅ Модальное окно закрывается при клике на кнопку закрытия на шаге чата



