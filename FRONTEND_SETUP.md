# 🌐 НАСТРОЙКА ФРОНТЕНДА

## ⚠️ **ТЕКУЩАЯ СИТУАЦИЯ:**

У вас **ДВА** фронтенда:

### **1. Публичный фронтенд (ai-bot-front-main)**
- Путь: `D:\project\Subcloudy\ai-bot-front-main`
- Технологии: Vue 3 + Vite + Tailwind
- Назначение: Публичный сайт для пользователей

### **2. Desktop фронтенд (subcloudy-desktop)**
- Путь: `D:\project\Subcloudy\subcloudy-desktop`
- Технологии: Vue 3 + Vite + Electron
- Назначение: Desktop приложение

---

## 🔧 **КАК ЗАПУСТИТЬ ПУБЛИЧНЫЙ ФРОНТЕНД:**

### **Вариант 1: Dev сервер (для разработки)**

```bash
cd D:\project\Subcloudy\ai-bot-front-main

# Создать .env файл
echo VITE_API_BASE=http://127.0.0.1:8000/api > .env

# Запустить dev сервер
npm run dev
```

Откроется на: **http://localhost:5173**

---

### **Вариант 2: Собранный фронтенд через Laravel**

```bash
cd D:\project\Subcloudy\ai-bot-front-main

# Собрать фронтенд
npm run build

# Скопировать в Laravel public
xcopy /E /Y dist\* ..\ai-bot-main\public\
```

Откроется на: **http://127.0.0.1:8000**

---

## 📍 **ТЕКУЩАЯ ПРОБЛЕМА:**

Сейчас на `http://127.0.0.1:8000` открывается **админ панель** `/login`, а не публичный фронтенд.

**Причина:** Laravel routes настроен так, что главная страница `/` редиректит на админку.

---

## ✅ **РЕШЕНИЕ:**

### **Шаг 1: Проверить routes**

Открыть `ai-bot-main/routes/web.php` и убедиться, что есть fallback роут для SPA:

```php
// В конце файла
Route::get('/{any}', function () {
    return view('spa');
})->where('any', '.*');
```

### **Шаг 2: Создать spa.blade.php**

Файл: `ai-bot-main/resources/views/spa.blade.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SubCloudy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

### **Шаг 3: Собрать и скопировать фронтенд**

```bash
cd ai-bot-front-main
npm run build
xcopy /E /Y dist\* ..\ai-bot-main\public\
```

---

## 🚀 **БЫСТРЫЙ СТАРТ (для вас сейчас):**

Поскольку у вас проблема с терминалом PowerShell, лучше использовать **CMD**:

```cmd
REM Открыть CMD как администратор

REM 1. Перейти в папку фронтенда
cd D:\project\Subcloudy\ai-bot-front-main

REM 2. Создать .env
echo VITE_API_BASE=http://127.0.0.1:8000/api > .env

REM 3. Собрать фронтенд
call npm run build

REM 4. Скопировать в public Laravel
xcopy /E /Y /I dist ..\ai-bot-main\public

REM 5. Проверить
start http://127.0.0.1:8000
```

---

## 📝 **ИТОГО:**

**Фронтенд:** `ai-bot-front-main` (Vue SPA)  
**Backend:** `ai-bot-main` (Laravel API)  
**Desktop:** `subcloudy-desktop` (Electron)  

**Доступ:**
- Dev фронтенд: `http://localhost:5173`  
- Prod фронтенд через Laravel: `http://127.0.0.1:8000`  
- Админ панель: `http://127.0.0.1:8000/login`  
- API: `http://127.0.0.1:8000/api`  

**Следующий шаг:** Запустите команды выше в **CMD** (не PowerShell)!





