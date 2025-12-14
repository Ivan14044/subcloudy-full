@echo off
chcp 65001 >nul
color 0A
title SubCloudy Desktop - Setup and Run

echo.
echo ═══════════════════════════════════════════════════════════
echo            SUBCLOUDY DESKTOP - SETUP AND RUN
echo ═══════════════════════════════════════════════════════════
echo.

REM Остановка старых процессов
echo [STEP 1/5] Stopping old Electron processes...
taskkill /F /IM electron.exe >nul 2>&1
echo            Done!

REM Очистка кэша и dist
echo [STEP 2/5] Cleaning cache and dist...
if exist "node_modules\.vite" rmdir /S /Q "node_modules\.vite" >nul 2>&1
if exist "dist" rmdir /S /Q "dist" >nul 2>&1
echo            Done!

REM Сборка приложения
echo [STEP 3/5] Building application (30-60 sec)...
call npm run build >nul 2>&1
if errorlevel 1 (
    echo            ERROR! Build failed!
    pause
    exit /b 1
)
echo            Done!

REM Копирование флагов
echo [STEP 4/5] Copying language flags...
if not exist "dist\renderer\img" mkdir "dist\renderer\img" >nul 2>&1
copy /Y "..\ai-bot-front-main\public\img\lang\ru.png" "dist\renderer\img\ru.png" >nul 2>&1
copy /Y "..\ai-bot-front-main\public\img\lang\en.png" "dist\renderer\img\en.png" >nul 2>&1
copy /Y "..\ai-bot-front-main\public\img\lang\uk.png" "dist\renderer\img\uk.png" >nul 2>&1

REM Проверка
if exist "dist\renderer\img\ru.png" (
    echo            ✓ ru.png
) else (
    echo            ✗ ru.png NOT FOUND!
)
if exist "dist\renderer\img\en.png" (
    echo            ✓ en.png
) else (
    echo            ✗ en.png NOT FOUND!
)
if exist "dist\renderer\img\uk.png" (
    echo            ✓ uk.png
) else (
    echo            ✗ uk.png NOT FOUND!
)

REM Запуск приложения
echo [STEP 5/5] Starting application...
echo.
echo ═══════════════════════════════════════════════════════════
echo                   APPLICATION STARTING
echo ═══════════════════════════════════════════════════════════
echo.

npm run dev
