@echo off
REM Build script for Vite
echo Building your project...
call npm run build
if %ERRORLEVEL% EQU 0 (
    echo.
    echo Build completed successfully!
    echo.
    echo Next steps:
    echo 1. Test with Lighthouse in Chrome (F12 ^> Lighthouse)
    echo 2. Clear cache and IndexedDB
    echo 3. Run audit in Incognito mode
    pause
) else (
    echo Build failed! Check the error above.
    pause
)
