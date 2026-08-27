@echo off
setlocal enabledelayedexpansion

REM Set PHP path
set PHP_PATH=d:\wampp64\bin\php\php\php.exe

REM Check if PHP exists
if not exist "%PHP_PATH%" (
    echo Error: PHP not found at %PHP_PATH%
    pause
    exit /b 1
)

REM Change to project directory
cd /d "d:\wampp64\www\urea"

REM Run the direct enum fix
echo.
echo ========================================
echo Applying supplier_ledgers enum fix
echo ========================================
echo.

"%PHP_PATH%" direct_enum_fix.php

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo SUCCESS: Enum fix applied
    echo ========================================
    echo.
) else (
    echo.
    echo ========================================
    echo FAILED: Enum fix did not complete
    echo ========================================
    echo.
)

pause
exit /b %errorlevel%
