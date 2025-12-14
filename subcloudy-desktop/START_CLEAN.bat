@echo off
chcp 65001 > nul

echo.
echo ======================================
echo   SubCloudy Desktop - Clean Start
echo ======================================
echo.

cd /d "%~dp0"

echo [1/3] Закрываю все экземпляры Electron...
taskkill /F /IM electron.exe 2>nul
timeout /t 2 /nobreak >nul

echo [2/3] Пересобираю приложение...
call npm run build
if errorlevel 1 (
    echo.
    echo [ERROR] Ошибка сборки!
    pause
    exit /b 1
)

echo [3/3] Запускаю приложение...
echo.
call npm start

echo.
echo Приложение закрыто.
pause



