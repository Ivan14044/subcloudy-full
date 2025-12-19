@echo off
chcp 65001 >nul 2>&1
title SubCloudy Frontend

echo ======================================
echo   SubCloudy Frontend
echo ======================================
echo.

cd /d "%~dp0"

REM Create .env if not exists
if not exist ".env" (
    echo [INFO] Creating .env file...
    REM Используем относительный путь /api для dev режима (Vite proxy перенаправит на Laravel сервер)
    REM Для production установите VITE_API_BASE в полный URL вашего API сервера
    echo VITE_API_BASE=/api > .env
    echo VITE_APP_TELEGRAM_BOT_ID=your_bot_id >> .env
    echo [INFO] Done
)

echo [INFO] Starting Vite dev server...
echo [INFO] Frontend will be available at: http://localhost:5173
echo.

call npm run dev

pause





