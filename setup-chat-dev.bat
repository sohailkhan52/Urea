@echo off
REM Warehouse Chat System - Development Setup Script
REM This script sets up the chat system for development on Windows

setlocal enabledelayedexpansion

echo.
echo ====================================================
echo   Warehouse Chat System - Development Setup
echo ====================================================
echo.

REM Step 1: Check prerequisites
echo [Step 1] Checking prerequisites...

where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: PHP not found. Please install PHP first.
    exit /b 1
)

where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: Composer not found. Please install Composer first.
    exit /b 1
)

echo OK - Prerequisites found

REM Step 2: Update .env
echo.
echo [Step 2] Configuring .env...

findstr /m "BROADCAST_DRIVER=log" .env >nul
if %ERRORLEVEL% EQU 0 (
    echo Updating BROADCAST_DRIVER to websocket...
    powershell -Command "(Get-Content .env) -replace 'BROADCAST_DRIVER=log', 'BROADCAST_DRIVER=websocket' | Set-Content .env"
    echo OK - BROADCAST_DRIVER updated
) else (
    echo Note: BROADCAST_DRIVER already configured
)

REM Step 3: Install WebSockets package
echo.
echo [Step 3] Installing Laravel WebSockets package...

call composer require beyondcode/laravel-websockets
if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to install WebSockets package
    exit /b 1
)

echo OK - WebSockets package installed

REM Step 4: Publish WebSockets config
echo.
echo [Step 4] Publishing WebSockets configuration...

call php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
if %ERRORLEVEL% NEQ 0 (
    echo Warning: Failed to publish configuration
)

REM Step 5: Run migrations
echo.
echo [Step 5] Running migrations...

call php artisan migrate
if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to run migrations
    exit /b 1
)

echo OK - Migrations completed

REM Step 6: Initialize conversations
echo.
echo [Step 6] Initializing warehouse conversations...

call php artisan chat:init-conversations
if %ERRORLEVEL% NEQ 0 (
    echo Error: Failed to initialize conversations
    exit /b 1
)

echo OK - Conversations initialized

REM Step 7: Clear caches
echo.
echo [Step 7] Clearing caches...

call php artisan cache:clear
call php artisan config:cache
call php artisan route:cache

echo OK - Caches cleared

REM Summary
echo.
echo ====================================================
echo   Setup Complete!
echo ====================================================
echo.

echo Next steps:
echo 1. Open two Command Prompt windows
echo 2. In window 1, run: php artisan serve
echo 3. In window 2, run: php artisan websockets:serve
echo 4. Visit http://localhost:8000/admin/chat
echo 5. Login as Super Admin and Regular Admin in different browsers
echo 6. Start chatting!
echo.

echo Documentation:
echo - README: CHAT_SYSTEM_README.md
echo - Deployment: CHAT_DEPLOYMENT_COMPLETE.md
echo - Implementation: WAREHOUSE_CHAT_IMPLEMENTATION.md
echo - WebSocket Setup: WEBSOCKET_SETUP_GUIDE.md
echo.

echo Happy chatting! (ctrl+c)
echo.

pause
