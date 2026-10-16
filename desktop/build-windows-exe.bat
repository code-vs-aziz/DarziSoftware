@echo off
setlocal EnableExtensions
title Darzi Baba ERP - Build Windows EXE
cd /d "%~dp0"

echo.
echo  Building Windows installer / portable EXE...
echo.

where node >nul 2>&1
if errorlevel 1 (
  echo [ERROR] Node.js chahiye: https://nodejs.org
  pause
  exit /b 1
)

if not exist "node_modules\electron-builder\package.json" (
  echo [..] npm install...
  call npm install
)

call npm run dist
if errorlevel 1 (
  echo [ERROR] Build fail.
  pause
  exit /b 1
)

echo.
echo [OK] Output folder: dist\
echo     Setup.exe / portable exe yahan milega.
echo.
explorer dist
pause
endlocal
