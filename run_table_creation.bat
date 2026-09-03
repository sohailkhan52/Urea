@echo off
REM Navigate to project directory
cd /d "d:\wampp64\www\urea"

REM Run the PHP script
php create_sales_return_tables.php

REM Capture the exit code
if %ERRORLEVEL% NEQ 0 (
    echo Table creation failed with exit code %ERRORLEVEL%
    exit /b 1
) else (
    echo Table creation completed successfully
    exit /b 0
)
