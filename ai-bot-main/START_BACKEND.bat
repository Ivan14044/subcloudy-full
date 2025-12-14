@echo off
chcp 65001 >nul 2>&1
title SubCloudy Backend (Laravel)

echo ======================================
echo   SubCloudy Backend (Laravel)
echo ======================================
echo.

cd /d "%~dp0"

echo [INFO] Starting Laravel development server...
echo [INFO] Backend will be available at: http://127.0.0.1:8000
echo.

php artisan serve --host=127.0.0.1 --port=8000

pause





