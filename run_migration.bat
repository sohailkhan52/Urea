@echo off
REM Run the supplier_ledgers enum migration

cd /d "d:\wampp64\www\urea"

echo.
echo ========================================
echo Running supplier_ledgers enum migration
echo ========================================
echo.

REM Try to run the migration
php artisan migrate --path=database/migrations/2026_08_25_000007_add_return_type_to_supplier_ledgers_enum.php

if errorlevel 1 (
    echo.
    echo ERROR: Migration failed
    echo.
    echo Attempting direct database fix...
    echo.
    php direct_enum_fix.php
)

echo.
echo ========================================
echo Verifying the enum was updated
echo ========================================
echo.

REM Create a simple verification script
php -d display_errors=1 -r "
try {
    \$conn = new PDO('mysql:host=127.0.0.1;dbname=urea', 'root', '');
    \$result = \$conn->query('DESCRIBE supplier_ledgers');
    \$result->setFetchMode(PDO::FETCH_ASSOC);
    
    while (\$row = \$result->fetch()) {
        if (\$row['Field'] === 'type') {
            echo 'Current type column: ' . \$row['Type'] . PHP_EOL;
            if (strpos(\$row['Type'], \"'return'\") !== false) {
                echo '✓ SUCCESS: The type enum now includes the return value!' . PHP_EOL;
            }
            break;
        }
    }
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo ========================================
echo Done
echo ========================================
echo.
pause
