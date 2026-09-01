@echo off
REM SkySoft Weather - Upload to GitHub
REM Run this AFTER: gh auth login

cd /d "%~dp0"

echo Checking GitHub login...
gh auth status
if errorlevel 1 (
    echo.
    echo Please login first:
    echo   gh auth login
    echo Then run this script again.
    pause
    exit /b 1
)

echo.
echo Creating GitHub repository and pushing...
gh repo create weather-forecast-system --public --source=. --remote=origin --push --description "SkySoft Weather - PHP weather forecast website with Open-Meteo API"

if errorlevel 1 (
    echo.
    echo If repo already exists, try:
    echo   git remote add origin https://github.com/YOUR_USERNAME/weather-forecast-system.git
    echo   git push -u origin main
)

echo.
echo Done!
pause
