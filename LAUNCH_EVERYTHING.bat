@echo off
chcp 65001 >nul 2>&1
color 0A
title SubCloudy Launcher

echo.
echo ═══════════════════════════════════════════════════════════
echo                    SUBCLOUDY LAUNCHER                      
echo ═══════════════════════════════════════════════════════════
echo.

REM Stop old processes
echo [STEP 1/5] Stopping old processes...
taskkill /F /IM php.exe 2>nul >nul
taskkill /F /IM node.exe 2>nul >nul
echo            Done!
timeout /t 1 /nobreak >nul

REM Configure frontend
echo [STEP 2/5] Configuring frontend...
cd /d "%~dp0ai-bot-front-main"
if not exist ".env" (
    echo VITE_API_BASE=http://127.0.0.1:8000/api > .env
    echo VITE_APP_TELEGRAM_BOT_ID=your_bot_id >> .env
)
echo            Done!

REM Start Laravel Backend
echo [STEP 3/5] Starting Laravel Backend...
echo            Opening backend window...
start "SubCloudy Backend [http://127.0.0.1:8000]" /D "%~dp0ai-bot-main" cmd /k "color 0B && echo ═══════════════════════════════════════════════════════════ && echo                 SUBCLOUDY BACKEND (Laravel) && echo ═══════════════════════════════════════════════════════════ && echo. && echo [INFO] Starting Laravel development server... && echo. && php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 3 /nobreak >nul
echo            Backend started!

REM Start Vite Frontend
echo [STEP 4/5] Starting Vite Frontend...
echo            Opening frontend window...
start "SubCloudy Frontend [http://localhost:5173]" /D "%~dp0ai-bot-front-main" cmd /k "color 0E && echo ═══════════════════════════════════════════════════════════ && echo                 SUBCLOUDY FRONTEND (Vite) && echo ═══════════════════════════════════════════════════════════ && echo. && echo [INFO] Starting Vite development server... && echo. && npm run dev"
timeout /t 5 /nobreak >nul
echo            Frontend starting!

REM Wait for services to start
echo [STEP 5/5] Waiting for services...
echo            Please wait 10 seconds...
timeout /t 10 /nobreak

REM Open browser
echo.
echo ═══════════════════════════════════════════════════════════
echo                    ALL SERVICES STARTED!                    
echo ═══════════════════════════════════════════════════════════
echo.
echo   Backend:  http://127.0.0.1:8000
echo   Frontend: http://localhost:5173
echo   Admin:    http://127.0.0.1:8000/login
echo.
echo ═══════════════════════════════════════════════════════════
echo.

echo Opening frontend in your browser...
start http://localhost:5173

echo.
echo [SUCCESS] Everything is running!
echo.
echo NOTE: Keep the backend and frontend windows open!
echo       Closing them will stop the services.
echo.
echo Press any key to close this launcher (services will keep running)...
pause >nul




