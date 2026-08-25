@echo off
echo Running Return Management Migrations...
echo.

php artisan migrate --path=database/migrations/2026_08_25_000001_create_sales_returns_table.php
php artisan migrate --path=database/migrations/2026_08_25_000002_create_sales_return_items_table.php
php artisan migrate --path=database/migrations/2026_08_25_000003_create_purchase_returns_table.php
php artisan migrate --path=database/migrations/2026_08_25_000004_create_purchase_return_items_table.php
php artisan migrate --path=database/migrations/2026_08_25_000005_create_return_sequences_tables.php
php artisan migrate --path=database/migrations/2026_08_25_000006_add_return_type_to_ledgers.php

echo.
echo Migrations completed!
echo.
echo Now running optimizations...
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo Done! You can now access the returns module.
pause
