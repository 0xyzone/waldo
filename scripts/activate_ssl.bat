@echo off
setlocal
cd /d "%~dp0"

:: Check for admin privileges
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ========================================================
    echo Requesting Administrator privileges...
    echo Please click "Yes" on the UAC prompt to continue.
    echo ========================================================
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process cmd -ArgumentList '/k `\"%~f0`\"' -Verb RunAs"
    exit /b
)

echo ========================================================
echo Installing Waldo Local Root Certificate to Windows Store...
echo ========================================================
certutil -addstore -f "Root" "D:\wamp64\bin\apache\apache2.4.65\conf\ssl\rootCA.crt"

echo.
echo ========================================================
echo Restarting WampServer Apache service (wampapache64)...
echo ========================================================
net stop wampapache64
net start wampapache64

echo.
echo ========================================================
echo [OK] SSL Setup Complete!
echo You can now access https://waldo and https://waldo/kamkaj
echo ========================================================
pause
