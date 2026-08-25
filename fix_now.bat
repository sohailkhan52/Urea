@echo off
setlocal

REM Get PHP path
set PHP_EXE=d:\wampp64\bin\php\php\php.exe
set PROJECT_DIR=d:\wampp64\www\urea

REM Check PHP exists
if not exist "%PHP_EXE%" (
    echo ERROR: PHP not found at %PHP_EXE%
    exit /b 1
)

REM Go to project directory
cd /d "%PROJECT_DIR%"

echo ========================================
echo SUPPLIER LEDGERS ENUM FIX
echo ========================================
echo.

REM Try Laravel migration first
echo Attempting Laravel migration...
echo.

"%PHP_EXE%" artisan migrate --path=database/migrations/2026_08_25_000007_add_return_type_to_supplier_ledgers_enum.php

if %errorlevel% equ 0 (
    echo.
    echo Migration succeeded!
    goto verify
)

echo.
echo Laravel migration failed. Trying direct database fix...
echo.

REM Try comprehensive fix
"%PHP_EXE%" comprehensive_enum_fix.php

if %errorlevel% equ 0 (
    goto verify
)

echo.
echo ERROR: Both methods failed!
exit /b 1

:verify
echo.
echo ========================================
echo VERIFYING THE FIX
echo ========================================
echo.

"%PHP_EXE%" diagnose_enum.php

echo.
echo ========================================
echo COMPLETE
echo ========================================
echo.

endlocal
