@echo off
REM ==============================================================
REM SUPPLIER PAYABLE PAYMENT FIX - BATCH SCRIPT
REM ==============================================================
REM
REM This script applies the migration to fix the duplicate entry error
REM when recording multiple partial payments against supplier purchases.
REM
REM ==============================================================

setlocal enabledelayedexpansion

echo.
echo ============================================================
echo  SUPPLIER PAYABLE PAYMENT FIX
echo ============================================================
echo.
echo This script will:
echo   1. Run pending migrations
echo   2. Fix the unique constraint on supplier_ledgers table
echo   3. Allow multiple partial payments per purchase
echo.

REM Change to project directory
cd /d "%~dp0"

REM Check if Laravel is installed
if not exist "artisan" (
    echo ERROR: artisan file not found. Make sure you're in the project root.
    pause
    exit /b 1
)

echo Step 1: Running migrations...
echo.

php artisan migrate

if !errorlevel! equ 0 (
    echo.
    echo ============================================================
    echo SUCCESS! The fix has been applied.
    echo ============================================================
    echo.
    echo You can now record multiple partial payments against the same
    echo supplier purchase without getting the "Duplicate entry" error.
    echo.
    echo Next steps:
    echo   1. Go to Admin Dashboard
    echo   2. Navigate to Payables
    echo   3. Select a supplier
    echo   4. Record multiple partial payments (should work now)
    echo.
    echo To verify, check:
    echo   - Transaction History shows all payments
    echo   - Ledger shows all payment entries
    echo   - Purchase status updates correctly (PARTIAL -> PAID)
    echo.
) else (
    echo.
    echo ============================================================
    echo ERROR: Migration failed
    echo ============================================================
    echo.
    echo If the command hung or timed out, try the manual fix:
    echo.
    echo Option 1: Use helper script
    echo   php run_migration_fix.php
    echo.
    echo Option 2: Manual SQL (phpMyAdmin)
    echo   - See PAYMENT_FIX_INSTRUCTIONS.md for SQL commands
    echo.
    echo Option 3: Check logs
    echo   - Review storage/logs/laravel.log for errors
    echo.
)

pause
