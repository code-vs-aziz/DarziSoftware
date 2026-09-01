@echo off
setlocal EnableExtensions
cd /d "%~dp0"

taskkill /IM Darzibaba.exe /F >nul 2>&1
taskkill /IM phpdesktop-chrome.exe /F >nul 2>&1

set "CFG=%~dp0install.cfg"
if not exist "%CFG%" goto :done

set "XAMPP_ROOT="
for /f "usebackq tokens=1,* delims==" %%A in (`findstr /i /b "Root=" "%CFG%"`) do set "XAMPP_ROOT=%%B"
set "XAMPP_ROOT=%XAMPP_ROOT: =%"

if not "%XAMPP_ROOT%"=="" if exist "%XAMPP_ROOT%\mysql_stop.bat" (
    call "%XAMPP_ROOT%\mysql_stop.bat"
)

:done
echo Darzibaba stopped.
pause
