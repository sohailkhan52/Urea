@echo off
cd /d "d:\wampp64\www\urea"
echo === Expense Management Setup ===
echo Running migrations...
php artisan migrate --force
echo.
echo Seeding permissions...
php artisan db:seed --class=PermissionSeeder --force
echo.
echo Seeding roles...
php artisan db:seed --class=RoleSeeder --force
echo.
echo === Setup Complete ===
echo Expense Management feature is ready. Please refresh your browser.
pause
