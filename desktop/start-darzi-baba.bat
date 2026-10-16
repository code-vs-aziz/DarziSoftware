@echo off
setlocal EnableExtensions
title Darzi Baba ERP Desktop
cd /d "%~dp0"

echo.
echo  ========================================
echo   Darzi Baba ERP - Desktop Launcher
echo  ========================================
echo.

REM --- PHP check (required) ---
where php >nul 2>&1
if errorlevel 1 (
  echo [ERROR] PHP nahi mila PATH mein.
  echo.
  echo Install karein: https://windows.php.net/download/  ya XAMPP
  echo Phir "php -v" Command Prompt mein chal kar check karein.
  echo.
  pause
  exit /b 1
)

for /f "tokens=*" %%v in ('php -v 2^>^&1 ^| findstr /i "PHP"') do (
  echo [OK] %%v
  goto :php_ok
)
:php_ok

REM --- Node / npm check (required for this launcher) ---
where node >nul 2>&1
if errorlevel 1 (
  echo [ERROR] Node.js nahi mila.
  echo Install: https://nodejs.org  ^(LTS^)
  echo.
  pause
  exit /b 1
)

where npm >nul 2>&1
if errorlevel 1 (
  echo [ERROR] npm nahi mila. Node.js dubara install karein.
  echo.
  pause
  exit /b 1
)

for /f "tokens=*" %%v in ('node -v') do echo [OK] Node %%v

REM --- First run: install Electron if missing ---
if not exist "node_modules\electron\package.json" (
  echo.
  echo [..] Pehli dafa setup - npm install chal raha hai...
  call npm install
  if errorlevel 1 (
    echo [ERROR] npm install fail hua.
    pause
    exit /b 1
  )
)

REM Optional: custom PHP / port
REM set DARZI_PHP_BIN=C:\php\php.exe
REM set DARZI_DESKTOP_PORT=8080

echo.
echo [..] Desktop app start ho rahi hai...
echo     ^(PHP server app khud start karegi agar 8080 free ho^)
echo.

call npm start
set EXITCODE=%ERRORLEVEL%

if not "%EXITCODE%"=="0" (
  echo.
  echo [ERROR] App band hui / start fail. Code=%EXITCODE%
  echo Check: MariaDB chal raha hai? .env DB settings theek hain?
  pause
)

endlocal
exit /b %EXITCODE%
