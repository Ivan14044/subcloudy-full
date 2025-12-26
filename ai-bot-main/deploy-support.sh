#!/bin/bash

# Скрипт деплоя улучшений системы поддержки
# Usage: bash deploy-support.sh

set -e

echo "====================================="
echo "Деплой системы поддержки"
echo "====================================="
echo ""

# Переходим в директорию backend
cd "$(dirname "$0")"

echo "[1/8] Создание backup БД..."
php artisan db:backup || echo "Warning: Backup command not found, skipping..."
echo "✓ Backup создан"
echo ""

echo "[2/8] Установка зависимостей Composer..."
composer install --no-dev --optimize-autoloader
echo "✓ Зависимости установлены"
echo ""

echo "[3/8] Запуск миграций..."
php artisan migrate --force
echo "✓ Миграции выполнены"
echo ""

echo "[4/8] Очистка кэша приложения..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✓ Кэш очищен"
echo ""

echo "[5/8] Кэширование конфигурации (production)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Конфигурация закэширована"
echo ""

echo "[6/8] Проверка прав доступа к storage..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || echo "Warning: Could not change ownership"
echo "✓ Права доступа установлены"
echo ""

echo "[7/8] Создание symlink для storage (если нужно)..."
php artisan storage:link || echo "Symlink already exists"
echo "✓ Symlink создан"
echo ""

echo "[8/8] Перезапуск queue worker (если используется)..."
php artisan queue:restart || echo "Queue not running"
echo "✓ Queue перезапущен"
echo ""

echo "====================================="
echo "✓ Деплой backend завершен успешно!"
echo "====================================="
echo ""
echo "Далее необходимо:"
echo "1. Перейти в ai-bot-front-main"
echo "2. Запустить: npm install && npm run build"
echo "3. Скопировать содержимое dist/ на сервер"
echo ""

