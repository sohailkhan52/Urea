@echo off
echo ========================================
echo Return Management System - Setup
echo ========================================
echo.

echo [1/5] Testing database connection...
php artisan db:show 2>nul
if errorlevel 1 (
    echo ERROR: Database connection failed!
    echo Please check your .env file and ensure:
    echo - MySQL/MariaDB is running
    echo - Database credentials are correct
    echo - Database exists
    pause
    exit /b 1
)
echo Database connection: OK
echo.

echo [2/5] Checking migration status...
php artisan migrate:status | findstr /C:"Pending" >nul
if errorlevel 1 (
    echo No pending migrations found.
) else (
    echo Found pending migrations.
)
echo.

echo [3/5] Running migrations...
php artisan migrate --force
if errorlevel 1 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo Migrations: OK
echo.

echo [4/5] Clearing caches...
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo Caches cleared: OK
echo.

echo [5/5] Verifying return routes...
php artisan route:list --name=returns | findstr /C:"returns"
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo You can now access:
echo - Sales Returns: /admin/sales/returns
echo - Purchase Returns: /admin/purchases/returns
echo.
pause
