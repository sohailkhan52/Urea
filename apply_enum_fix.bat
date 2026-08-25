@echo off
REM Change to project directory
cd /d "d:\wampp64\www\urea"

REM Run the migration to add return type to supplier_ledgers
echo.
echo Applying supplier_ledgers enum fix...
echo.

php artisan migrate --path=database/migrations/2026_08_25_000007_add_return_type_to_supplier_ledgers_enum.php

if errorlevel 1 (
    echo.
    echo ERROR: Migration failed!
    echo.
    pause
    exit /b 1
)

echo.
echo Migration completed successfully!
echo.
echo Verifying the enum was updated...
echo.

REM Verify the table structure
php -r "
$conn = new mysqli('127.0.0.1', 'root', '', 'urea');
if (\$conn->connect_error) {
    die('Connection failed: ' . \$conn->connect_error);
}
\$result = \$conn->query('DESCRIBE supplier_ledgers');
if (\$result) {
    while (\$row = \$result->fetch_assoc()) {
        if (\$row['Field'] == 'type') {
            echo 'Current type column definition: ' . \$row['Type'] . PHP_EOL;
        }
    }
}
\$conn->close();
"

echo.
echo Done!
pause
