# Правильный процесс развертывания фронтенда

## Проблемы в текущем процессе

### ❌ Что было неправильно:
1. **Неправильная директория деплоя**: Деплоил в `/var/www/html/`, но Nginx настроен на `/var/www/subcloudy/backend/public/`
2. **Нет резервной копии**: Удалял файлы без создания бэкапа
3. **Нет проверки**: Не проверял работоспособность после деплоя
4. **Нет версионирования**: Нет возможности отката
5. **Проблемы с кэшированием**: Не настраивал заголовки кэширования

## ✅ Правильный процесс развертывания

### 1. Подготовка
```bash
# Проверка структуры на сервере
ssh root@193.111.63.104
cd /var/www/subcloudy/ai-bot-front-main
git pull  # если используется git
# или загрузить измененные файлы через scp
```

### 2. Создание резервной копии
```bash
# Создать бэкап текущей версии
BACKUP_DIR="/var/www/subcloudy/backend/public.backup.$(date +%Y%m%d_%H%M%S)"
cp -r /var/www/subcloudy/backend/public "$BACKUP_DIR"
echo "Backup created: $BACKUP_DIR"
```

### 3. Сборка проекта
```bash
# На сервере или локально
cd /var/www/subcloudy/ai-bot-front-main
npm install  # если обновились зависимости
npm run build
```

### 4. Деплой с минимальным downtime
```bash
# Вариант 1: Атомарное копирование (рекомендуется)
# Создать временную директорию
TEMP_DIR="/var/www/subcloudy/backend/public.new"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Скопировать новые файлы
cp -r /var/www/subcloudy/ai-bot-front-main/dist/* "$TEMP_DIR"

# Атомарная замена (минимальный downtime)
mv /var/www/subcloudy/backend/public /var/www/subcloudy/backend/public.old
mv "$TEMP_DIR" /var/www/subcloudy/backend/public

# Удалить старую версию после проверки
# rm -rf /var/www/subcloudy/backend/public.old
```

### 5. Установка прав доступа
```bash
chown -R www-data:www-data /var/www/subcloudy/backend/public
find /var/www/subcloudy/backend/public -type d -exec chmod 755 {} \;
find /var/www/subcloudy/backend/public -type f -exec chmod 644 {} \;
```

### 6. Проверка работоспособности
```bash
# Проверить, что файлы на месте
test -f /var/www/subcloudy/backend/public/index.html && echo "OK" || echo "ERROR"

# Проверить доступность сайта
curl -I https://subcloudy.com

# Проверить логи
tail -f /var/log/nginx/error.log
```

### 7. Настройка кэширования (в Nginx)
```nginx
# В конфиге Nginx добавить:
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}

# Для HTML - без кэширования или короткий кэш
location ~* \.html$ {
    expires -1;
    add_header Cache-Control "no-cache, no-store, must-revalidate";
}
```

### 8. Откат при проблемах
```bash
# Если что-то пошло не так
mv /var/www/subcloudy/backend/public /var/www/subcloudy/backend/public.broken
mv /var/www/subcloudy/backend/public.old /var/www/subcloudy/backend/public
# или из бэкапа
mv "$BACKUP_DIR" /var/www/subcloudy/backend/public
```

## Автоматизированный скрипт деплоя

Создать файл `deploy.sh` на сервере:

```bash
#!/bin/bash
set -e  # Остановка при ошибке

PROJECT_DIR="/var/www/subcloudy/ai-bot-front-main"
PUBLIC_DIR="/var/www/subcloudy/backend/public"
BACKUP_DIR="/var/www/subcloudy/backend/public.backup.$(date +%Y%m%d_%H%M%S)"

echo "=== Starting deployment ==="

# 1. Backup
echo "Creating backup..."
cp -r "$PUBLIC_DIR" "$BACKUP_DIR"
echo "Backup created: $BACKUP_DIR"

# 2. Build
echo "Building project..."
cd "$PROJECT_DIR"
npm install --production
npm run build

# 3. Deploy
echo "Deploying..."
TEMP_DIR="${PUBLIC_DIR}.new"
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"
cp -r "$PROJECT_DIR/dist"/* "$TEMP_DIR"

# Atomic swap
mv "$PUBLIC_DIR" "${PUBLIC_DIR}.old"
mv "$TEMP_DIR" "$PUBLIC_DIR"

# 4. Permissions
echo "Setting permissions..."
chown -R www-data:www-data "$PUBLIC_DIR"
find "$PUBLIC_DIR" -type d -exec chmod 755 {} \;
find "$PUBLIC_DIR" -type f -exec chmod 644 {} \;

# 5. Verify
echo "Verifying..."
if [ -f "$PUBLIC_DIR/index.html" ]; then
    echo "✅ Deployment successful!"
    # Удалить старую версию после успешной проверки
    # rm -rf "${PUBLIC_DIR}.old"
else
    echo "❌ Deployment failed! Rolling back..."
    mv "$PUBLIC_DIR" "${PUBLIC_DIR}.broken"
    mv "${PUBLIC_DIR}.old" "$PUBLIC_DIR"
    exit 1
fi

echo "=== Deployment completed ==="
```

## Локальный деплой (с Windows)

Если деплоите с локальной машины:

```powershell
# 1. Сборка
cd ai-bot-front-main
npm run build

# 2. Создание архива
Compress-Archive -Path dist\* -DestinationPath dist.zip -Force

# 3. Загрузка на сервер
scp dist.zip root@193.111.63.104:/tmp/

# 4. На сервере выполнить:
ssh root@193.111.63.104
cd /var/www/subcloudy/ai-bot-front-main
unzip -o /tmp/dist.zip -d dist/
# Затем выполнить deploy.sh
```

## Рекомендации

1. **Использовать Git**: Хранить исходники в Git, деплоить через `git pull`
2. **CI/CD**: Настроить автоматический деплой через GitHub Actions или GitLab CI
3. **Версионирование**: Добавить версию в `package.json` и отображать на сайте
4. **Мониторинг**: Настроить мониторинг доступности сайта
5. **Логирование**: Логировать все деплои для аудита


