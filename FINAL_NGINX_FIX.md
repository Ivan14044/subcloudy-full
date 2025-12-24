# Финальное решение проблемы Nginx + Laravel

## Проблема:
- Все API запросы (`/api/*`) и админ-панель (`/admin/*`) возвращают 404 "File not found"
- В логах Nginx: `Primary script unknown` от PHP-FPM
- Laravel работает напрямую через PHP CLI

## Причина:
Nginx не может правильно передать запросы в PHP-FPM для Laravel. Проблема в том, что `try_files` и вложенные `location` блоки не работают правильно с FastCGI.

## Решение:
Использовать простую конфигурацию, которая передает все запросы к `/api/*`, `/admin/*`, `/auth/*` напрямую в Laravel `index.php` через FastCGI, без использования `try_files` или вложенных `location` блоков.

## Рекомендуемая конфигурация:

```nginx
location ~ ^/(api|admin|auth)(/.*)?$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /var/www/subcloudy/backend/ai-bot-main/public/index.php;
    fastcgi_param DOCUMENT_ROOT /var/www/subcloudy/backend/ai-bot-main/public;
    fastcgi_param REQUEST_URI $request_uri;
    fastcgi_param QUERY_STRING $query_string;
}
```

## Важно:
- Убрать `root` или `alias` из location блока для `/api/*`, `/admin/*`, `/auth/*`
- Убрать `try_files` из location блока
- Использовать фиксированный `SCRIPT_FILENAME` с абсолютным путем к Laravel `index.php`
- Определить `fastcgi_param SCRIPT_FILENAME` ПОСЛЕ `include fastcgi_params;`, чтобы перезаписать стандартное значение

## Статус:
Проблема все еще не решена. Требуется дополнительная диагностика или помощь системного администратора для проверки конфигурации PHP-FPM и прав доступа.

