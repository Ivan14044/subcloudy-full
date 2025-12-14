# 📦 Installation Guide - SubCloudy Desktop

## Для пользователей (End Users)

### Windows

1. **Скачайте установщик**
   - Перейдите на сайт: https://subcloudy.com/desktop
   - Скачайте `SubCloudy-Setup-{version}.exe`

2. **Установка**
   - Запустите скачанный файл
   - Следуйте инструкциям установщика
   - Выберите папку установки (опционально)
   - Дождитесь завершения установки

3. **Первый запуск**
   - Найдите ярлык "SubCloudy" на рабочем столе
   - Или запустите из меню Пуск
   - Войдите используя свои учетные данные с subcloudy.com

### macOS

1. **Скачайте DMG файл**
   - Перейдите на сайт: https://subcloudy.com/desktop
   - Скачайте `SubCloudy-{version}.dmg`

2. **Установка**
   - Откройте скачанный DMG файл
   - Перетащите SubCloudy в папку Applications
   - Дождитесь завершения копирования

3. **Первый запуск**
   - Откройте Applications
   - Найдите и запустите SubCloudy
   - При предупреждении "App от неизвестного разработчика":
     * Откройте Системные настройки → Безопасность
     * Нажмите "Открыть в любом случае"
   - Войдите используя свои учетные данные

### Linux (Ubuntu/Debian)

**Метод 1: DEB пакет**

```bash
# Скачайте .deb файл
wget https://subcloudy.com/downloads/SubCloudy-{version}.deb

# Установите
sudo dpkg -i SubCloudy-{version}.deb

# Установите зависимости если нужно
sudo apt-get install -f

# Запустите
subcloudy
```

**Метод 2: AppImage**

```bash
# Скачайте AppImage
wget https://subcloudy.com/downloads/SubCloudy-{version}.AppImage

# Сделайте исполняемым
chmod +x SubCloudy-{version}.AppImage

# Запустите
./SubCloudy-{version}.AppImage
```

---

## Для разработчиков (Developers)

### Системные требования

- **Node.js**: v18.0.0 или выше
- **npm**: v9.0.0 или выше
- **Git**
- **Backend API**: Laravel проект должен быть запущен

### 1. Клонирование проекта

```bash
git clone https://github.com/subcloudy/subcloudy-desktop.git
cd subcloudy-desktop
```

### 2. Установка зависимостей

```bash
npm install
```

Это установит:
- Electron
- Vue 3
- Pinia
- TypeScript
- Vite
- electron-builder
- И другие зависимости

### 3. Настройка окружения

Создайте файл `.env`:

```bash
cp .env.example .env
```

Отредактируйте `.env`:

```env
# URL вашего локального backend API
API_URL=http://127.0.0.1:8000/api

NODE_ENV=development
```

### 4. Запуск в режиме разработки

```bash
npm run dev
```

Это запустит:
- Vite dev server на порту 5175
- Electron приложение с hot-reload

### 5. Работа с Backend

Убедитесь, что Laravel backend запущен:

```bash
cd ../ai-bot-main
php artisan serve
```

Backend должен быть доступен на `http://127.0.0.1:8000`

### 6. Разработка

**Структура проекта:**

```
src/
├── main/       # Electron Main Process (Node.js)
├── preload/    # Preload scripts (Bridge)
└── renderer/   # Vue.js UI (Browser)
```

**Hot Reload:**
- Изменения в `renderer/` применяются автоматически
- Изменения в `main/` требуют перезапуска (Ctrl+C, npm run dev)

### 7. Сборка для тестирования

**Сборка без упаковки:**

```bash
npm run build
```

**Сборка и упаковка:**

```bash
# Текущая платформа
npm run dist

# Конкретная платформа
npm run dist:win    # Windows
npm run dist:mac    # macOS  
npm run dist:linux  # Linux
```

Результат будет в папке `dist-electron/`

### 8. Отладка

**Renderer Process (Vue.js):**

Раскомментируйте в `src/main/windows/mainWindow.ts`:

```typescript
mainWindow.webContents.openDevTools();
```

**Main Process:**

Используйте `console.log()` - вывод будет в терминале

**VS Code Debug:**

Создайте `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "type": "node",
      "request": "launch",
      "name": "Electron Main",
      "runtimeExecutable": "${workspaceFolder}/node_modules/.bin/electron",
      "runtimeArgs": [".", "--remote-debugging-port=9223"],
      "preLaunchTask": "npm: build:main"
    }
  ]
}
```

---

## Настройка Backend (Laravel)

### 1. Добавление DesktopController

Контроллер уже создан в `ai-bot-main/app/Http/Controllers/Api/DesktopController.php`

### 2. Добавление роутов

Роуты уже добавлены в `ai-bot-main/routes/api.php`:

```php
Route::middleware('auth:sanctum')->prefix('desktop')->group(function () {
    Route::post('/auth', [DesktopController::class, 'auth']);
    Route::post('/service-url', [DesktopController::class, 'getSecureServiceUrl']);
    Route::get('/my-services', [DesktopController::class, 'myServices']);
    Route::post('/log', [DesktopController::class, 'logActivity']);
});
```

### 3. Настройка CORS

В `ai-bot-main/config/cors.php` добавьте:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'desktop/*'],

'allowed_origins' => [
    'http://localhost:5175',  // Vite dev server
    'file://*',               // Electron production
],
```

### 4. Миграции

Убедитесь что все миграции выполнены:

```bash
php artisan migrate
```

### 5. Тестирование endpoints

```bash
# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get services (with token)
curl -X GET http://127.0.0.1:8000/api/desktop/my-services \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Распространение (Distribution)

### Code Signing

**Windows (требуется сертификат):**

```bash
export CSC_LINK=/path/to/certificate.pfx
export CSC_KEY_PASSWORD=your_password
npm run dist:win
```

**macOS (требуется Apple Developer ID):**

```bash
export APPLE_ID=your@email.com
export APPLE_ID_PASSWORD=app-specific-password
export APPLE_TEAM_ID=XXXXXXXXXX
npm run dist:mac
```

### Публикация обновлений

1. Соберите новую версию
2. Загрузите файлы на сервер обновлений
3. Обновите `latest.yml` / `latest-mac.yml`

---

## Troubleshooting

### Electron не запускается

```bash
# Очистите кеш
rm -rf node_modules dist
npm install
npm run dev
```

### Ошибка подключения к API

- Проверьте что backend запущен: `php artisan serve`
- Проверьте URL в `.env`
- Проверьте CORS настройки
- Проверьте firewall/antivirus

### Ошибка при сборке

```bash
# Обновите electron-builder
npm update electron-builder

# Очистите build кеш
rm -rf dist-electron

# Пересоберите
npm run build
npm run dist
```

### Иконка не отображается

Убедитесь что файлы иконок существуют:
- `resources/icons/icon.ico` (Windows)
- `resources/icons/icon.icns` (macOS)
- `resources/icons/icon.png` (Linux)

---

## Поддержка

- 📧 Email: support@subcloudy.com
- 💬 Telegram: @subcloudy_support
- 🌐 Website: https://subcloudy.com
- 📚 Docs: https://docs.subcloudy.com


