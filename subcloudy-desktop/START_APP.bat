@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion
title SubCloudy Desktop Launcher

echo.
echo ======================================
echo   SubCloudy Desktop
echo ======================================
echo.
echo [%date% %time%] Starting application...
echo.

set APP_DIR=%~dp0
cd /d "%APP_DIR%"

REM Kill existing Electron processes
echo [INFO] Checking for running Electron instances...
tasklist /FI "IMAGENAME eq electron.exe" 2>nul | find /I "electron.exe" >nul
if %errorlevel% equ 0 (
    echo [INFO] Closing previous Electron instances...
    taskkill /F /IM electron.exe >nul 2>&1
    timeout /t 2 /nobreak >nul
    echo [INFO] Done
) else (
    echo [INFO] No running instances found
)

REM Check node_modules
if not exist "node_modules" (
    echo [INFO] node_modules not found. Installing dependencies...
    call npm install
    if errorlevel 1 (
        echo [ERROR] Failed to install dependencies.
        pause
        exit /b 1
    )
)

REM Build project
echo [INFO] Building application...
call npm run build
if errorlevel 1 (
    echo [ERROR] Build failed.
    pause
    exit /b 1
)

REM Start application
echo [INFO] Starting SubCloudy Desktop...
echo.
call npm start

echo.
echo [INFO] Application closed.
pause
