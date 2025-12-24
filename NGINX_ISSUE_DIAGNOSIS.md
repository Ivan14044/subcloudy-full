# Диагностика проблемы Nginx + Laravel

## Текущая ситуация:
- Все API запросы (`/api/*`) и админ-панель (`/admin/*`) возвращают 404 "File not found"
- В логах Nginx: `Primary script unknown` от PHP-FPM
- Laravel работает напрямую через PHP CLI

## Проверено:
1. ✅ Файл `/var/www/subcloudy/backend/ai-bot-main/public/index.php` существует
2. ✅ Права доступа корректны (`www-data:www-data 755`)
3. ✅ PHP-FPM может прочитать файл (`sudo -u www-data test -r` → `READABLE`)
4. ✅ Laravel работает напрямую через PHP CLI

## Проблема:
PHP-FPM возвращает "Primary script unknown", что означает, что он не может найти скрипт по указанному пути в `SCRIPT_FILENAME`.

## Возможные причины:
1. **`chdir` в PHP-FPM**: PHP-FPM использует `chdir = /var/www`, что может влиять на разрешение путей
2. **`open_basedir`**: Может ограничивать доступ к файлам
3. **Порядок параметров FastCGI**: `fastcgi_param SCRIPT_FILENAME` может перезаписываться
4. **Проблема с `root` в location блоке**: Может конфликтовать с глобальным `root`

## Попытки исправления:
1. ✅ Использование фиксированного `SCRIPT_FILENAME` в location блоке
2. ✅ Перемещение `fastcgi_param SCRIPT_FILENAME` после `include fastcgi_params;`
3. ✅ Удаление вложенных `location` блоков
4. ✅ Проверка прав доступа и существования файла

## Следующие шаги:
1. Проверить логи PHP-FPM на наличие более детальной информации
2. Попробовать использовать `alias` вместо `root` в location блоке
3. Проверить настройки `open_basedir` в PHP-FPM
4. Попробовать использовать `fastcgi_param DOCUMENT_ROOT` явно
5. Проверить, не конфликтует ли глобальный `root` с `root` в location блоке


