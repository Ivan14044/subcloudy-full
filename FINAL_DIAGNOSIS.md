# Финальная диагностика проблемы API и админ-панели

## Дата: 24.12.2025

### ✅ Что исправлено:

1. **API Base URL в bootstrap.js** ✅
   - Исправлен для использования относительного пути `/api` в продакшене
   - В логах видно: `[Bootstrap] Production mode - API Base URL: /api` ✅

2. **Конфигурация Nginx частично обновлена** ⚠️
   - Попытки обновить конфигурацию через PowerShell не удались из-за проблем с экранированием
   - Текущая конфигурация неполная

### ❌ Текущие проблемы:

1. **Все API запросы возвращают 404** ❌
   - Ошибка: "File not found"
   - Причина: Nginx не может найти PHP скрипт Laravel
   - Ошибка в логах: "Primary script unknown"

2. **Админ-панель возвращает 404** ❌
   - Ошибка: "File not found"
   - Причина: Та же - Nginx не может найти PHP скрипт Laravel

### 🔍 Диагностика:

**Laravel работает:**
- ✅ Laravel Framework 10.48.29 установлен
- ✅ Routes существуют (проверено через `php artisan route:list`)
- ✅ `.env` файл существует
- ✅ `index.php` существует в `/var/www/subcloudy/backend/ai-bot-main/public/index.php`

**Проблема в Nginx:**
- ❌ Конфигурация Nginx не правильно настроена для обработки `/api/*` и `/admin/*`
- ❌ Для этих путей используется неправильный `root` (Vue фронтенд вместо Laravel)
- ❌ Обработка PHP через FastCGI не настроена правильно

### 📝 Решение:

Нужно вручную на сервере выполнить команду для создания правильной конфигурации Nginx:

```bash
# На сервере выполнить:
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

### ⚠️ Важно:

Из-за проблем с экранированием в PowerShell через plink, конфигурация Nginx не может быть обновлена автоматически. Нужно выполнить команду выше **напрямую на сервере через SSH**.

### 📊 Статус:

- ✅ API Base URL исправлен
- ✅ Фронтенд задеплоен
- ⚠️ Конфигурация Nginx требует ручного исправления на сервере
- ❌ API и админ-панель не работают до исправления Nginx


