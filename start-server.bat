@echo off
echo ========================================
echo   PHP Development Server - WebSIM
echo ========================================
echo.
echo Starting server on: http://localhost:9999
echo.
echo IMPORTANT:
echo - Make sure XAMPP MySQL is running!
echo - Server root: %~dp0
echo - API test: http://localhost:9999/api/test.php
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.
cd /d %~dp0
php -S localhost:9999 -t .
pause

