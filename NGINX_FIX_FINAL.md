# Финальное исправление конфигурации Nginx

## Проблема

Ошибка "Primary script unknown" в логах Nginx означает, что PHP-FPM не может найти скрипт. Проблема в том, что для `/api` и `/admin` нужно использовать правильный `root` - директорию Laravel, а не Vue фронтенда.

## Текущая конфигурация

Текущая конфигурация использует:
- `root /var/www/subcloudy/backend/public;` (Vue фронтенд)
- Для `/api` и `/admin` нужно: `root /var/www/subcloudy/backend/ai-bot-main/public;` (Laravel)

## Правильная конфигурация

Нужно создать правильную конфигурацию Nginx, где:
1. Для `/api/*`, `/admin/*`, `/auth/*` используется `root /var/www/subcloudy/backend/ai-bot-main/public;`
2. Добавлена обработка PHP через FastCGI
3. Для всех остальных путей используется Vue Router

## Команда для исправления на сервере

Выполнить на сервере:

```bash
cat > /etc/nginx/sites-available/subcloudy << 'NGINX_EOF'
server {
    listen 80;
    listen [::]:80;
    server_name subcloudy.com 193.111.63.104;
    return 301 https://subcloudy.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    client_max_body_size 50M;
    server_name subcloudy.com 193.111.63.104;

    root /var/www/subcloudy/backend/public;
    index index.php index.html;
    charset utf-8;

    ssl_certificate /etc/letsencrypt/live/subcloudy.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/subcloudy.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_trusted_certificate /etc/letsencrypt/live/subcloudy.com/chain.pem;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location ~ ^/(api|admin|auth)(/.*)?$ {
        root /var/www/subcloudy/backend/ai-bot-main/public;
        try_files $uri $uri/ /index.php?$query_string;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
NGINX_EOF

nginx -t && systemctl reload nginx
```

## Проверка

После применения конфигурации проверить:
1. `curl -I https://subcloudy.com/api/services` - должен вернуть 200 или правильный ответ от Laravel
2. `curl -I https://subcloudy.com/admin/login` - должен вернуть 200 или правильный ответ от Laravel
3. Проверить логи: `tail -f /var/log/nginx/error.log`

