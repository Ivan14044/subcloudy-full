# Структура деплоя фронтенда

## Директории на сервере

### Исходники (для разработки):
- `/var/www/subcloudy/ai-bot-front-main/` - **ОСНОВНАЯ ДИРЕКТОРИЯ С ИСХОДНИКАМИ**
  - Здесь находятся все `.vue`, `.ts`, `.json` файлы
  - Здесь выполняется `npm run build`
  - **ВСЕ ИЗМЕНЕНИЯ ДОЛЖНЫ БЫТЬ ЗДЕСЬ**

### Публичная директория (для Nginx):
- `/var/www/subcloudy/backend/public/` - **ОТСЮДА ЗАГРУЖАЕТСЯ САЙТ**
  - Nginx настроен на `root /var/www/subcloudy/backend/public;`
  - Сюда копируются файлы из `dist/` после сборки
  - **СЮДА НЕ РЕДАКТИРУЕМ НАПРЯМУЮ!**

### Другие директории (не используются):
- `/var/www/subcloudy/backend/frontend/` - старая версия, не используется
- `/var/www/subcloudy/backend/frontend.backup./` - бэкап, не используется

## Процесс деплоя

1. **Локально**: Редактируем файлы в `ai-bot-front-main/src/`
2. **Загружаем на сервер**: `scp` файлы в `/var/www/subcloudy/ai-bot-front-main/src/`
3. **Собираем**: `cd /var/www/subcloudy/ai-bot-front-main && npm run build`
4. **Деплоим**: `cp -r dist/* /var/www/subcloudy/backend/public/`

## Важно!

- **ВСЕГДА** работаем с `/var/www/subcloudy/ai-bot-front-main/`
- **НИКОГДА** не редактируем файлы в `/var/www/subcloudy/backend/public/` напрямую
- После изменений **ОБЯЗАТЕЛЬНО** пересобираем и копируем в `public/`


