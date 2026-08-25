@echo off
echo ============================================
echo Stock Request System - Deployment Script
echo ============================================
echo.

echo [1/5] Running migrations...
php artisan migrate
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo Migrations completed successfully!
echo.

echo [2/5] Seeding permissions...
php artisan db:seed --class=PermissionSeeder
if %errorlevel% neq 0 (
    echo ERROR: Permission seeding failed!
    pause
    exit /b 1
)
echo Permissions seeded successfully!
echo.

echo [3/5] Seeding roles...
php artisan db:seed --class=RoleSeeder
if %errorlevel% neq 0 (
    echo ERROR: Role seeding failed!
    pause
    exit /b 1
)
echo Roles seeded successfully!
echo.

echo [4/5] Clearing caches...
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
echo Caches cleared successfully!
echo.

echo [5/5] Verifying installation...
php artisan route:list | findstr "stock-requests"
echo.

echo ============================================
echo Deployment Complete!
echo ============================================
echo.
echo Next steps:
echo 1. Create Blade views in resources/views/admin/stock-requests/
echo 2. Add "Stock Requests" link to sidebar (within @multiwarehouse directive)
echo 3. Test with 2+ active warehouses
echo.
echo For more information, see STOCK_REQUEST_IMPLEMENTATION_GUIDE.md
echo.
pause
