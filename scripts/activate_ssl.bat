@echo off
title Waldo SSL Setup and Apache Restart

:: Check for admin privileges
net session >nul 2>&1
if %errorLevel% == 0 (
    goto :admin
) else (
    echo Requesting Administrator privileges to install certificate and restart Apache...
    powershell -Command "Start-Process cmd -ArgumentList '/c \"\"%~f0\"\"' -Verb RunAs"
    exit /b
)

:admin
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
echo SSL Setup Complete!
echo You can now access https://waldo and https://waldo/kamkaj
echo ========================================================
timeout /t 5
