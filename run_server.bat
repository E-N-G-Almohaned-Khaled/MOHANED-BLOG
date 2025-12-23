@echo off
cd /d "%~dp0"
echo.
echo ========================================
echo   Mohaned Blog Server is Running!
echo   Address: http://localhost:9999
echo ========================================
echo.
echo Opening browser...
start http://localhost:9999
"C:\xampp\php\php.exe" -S localhost:9999 -t .
pause
