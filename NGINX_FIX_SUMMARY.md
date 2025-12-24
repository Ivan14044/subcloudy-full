# Проблема с Nginx и Laravel

## Текущая ситуация:
- Все API запросы (`/api/*`) и админ-панель (`/admin/*`) возвращают 404 "File not found"
- В логах Nginx: `Primary script unknown` от PHP-FPM
- Laravel работает напрямую через PHP CLI

## Причина:
Nginx не может правильно передать запросы в PHP-FPM для Laravel. Проблема в конфигурации `location` блоков.

## Решение:
Нужно использовать правильную конфигурацию Nginx, которая:
1. Перехватывает все запросы к `/api/*`, `/admin/*`, `/auth/*`
2. Передает их напрямую в Laravel `index.php` через FastCGI
3. Использует правильный `SCRIPT_FILENAME` и `REQUEST_URI`

## Текущая конфигурация:
```nginx
location ~ ^/(api|admin|auth)(/.*)?$ {
    root /var/www/subcloudy/backend/ai-bot-main/public;
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME /var/www/subcloudy/backend/ai-bot-main/public/index.php;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_param QUERY_STRING $query_string;
    include fastcgi_params;
}
```

## Проблема:
PHP-FPM все еще возвращает "Primary script unknown", что означает, что он не может найти файл по указанному пути.

## Возможные причины:
1. PHP-FPM не может прочитать файл (права доступа)
2. `open_basedir` ограничивает доступ
3. Неправильный путь к файлу
4. Проблема с `chdir` в PHP-FPM

## Следующие шаги:
1. Проверить логи PHP-FPM на наличие более детальной информации
2. Проверить права доступа на файл и директорию
3. Проверить настройки `open_basedir` в PHP-FPM
4. Попробовать использовать `alias` вместо `root` в location блоке

