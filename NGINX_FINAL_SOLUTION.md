# Финальное решение проблемы Nginx

## Проблема

Ошибка "Primary script unknown" означает, что PHP-FPM не может найти скрипт. Проблема в том, что вложенные location блоки с регулярными выражениями в Nginx работают не так, как ожидается.

## Решение

Нужно использовать отдельные location блоки для каждого пути (api, admin, auth) с правильным root.

## Правильная конфигурация

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

    location ~ ^/api(/.*)?$ {
        root /var/www/subcloudy/backend/ai-bot-main/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/admin(/.*)?$ {
        root /var/www/subcloudy/backend/ai-bot-main/public;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/auth(/.*)?$ {
        root /var/www/subcloudy/backend/ai-bot-main/public;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        set $fastcgi_root /var/www/subcloudy/backend/public;
        if ($request_uri ~ ^/(api|admin|auth)) {
            set $fastcgi_root /var/www/subcloudy/backend/ai-bot-main/public;
        }
        root $fastcgi_root;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $fastcgi_root$fastcgi_script_name;
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

## Альтернативное решение (без if)

Если `if` не работает, использовать отдельные location блоки для PHP:

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
    }
    
    location ~ ^/(api|admin|auth)(/.*)?\.php$ {
        root /var/www/subcloudy/backend/ai-bot-main/public;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ \.php$ {
        root /var/www/subcloudy/backend/public;
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

## Важно

Из-за ограничений PowerShell через plink, конфигурацию нужно применить **напрямую на сервере через SSH**.

