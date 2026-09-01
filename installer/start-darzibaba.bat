@echo off
setlocal EnableExtensions
cd /d "%~dp0"

set "CFG=%~dp0install.cfg"
if not exist "%CFG%" (
    echo install.cfg not found. Re-run Darzibaba Setup.
    pause
    exit /b 1
)

set "XAMPP_ROOT="
for /f "usebackq tokens=1,* delims==" %%A in (`findstr /i /b "Root=" "%CFG%"`) do set "XAMPP_ROOT=%%B"
set "XAMPP_ROOT=%XAMPP_ROOT: =%"

if "%XAMPP_ROOT%"=="" (
    echo XAMPP path missing in install.cfg
    pause
    exit /b 1
)

if exist "%XAMPP_ROOT%\mysql_start.bat" (
    call "%XAMPP_ROOT%\mysql_start.bat"
) else (
    echo Note: Start MySQL from XAMPP Control Panel if connection fails.
)

if exist "%~dp0Darzibaba.exe" (
    start "" "%~dp0Darzibaba.exe"
    exit /b 0
)

if exist "%~dp0phpdesktop-chrome.exe" (
    start "" "%~dp0phpdesktop-chrome.exe"
    exit /b 0
)

echo Darzibaba.exe not found in %~dp0
pause
exit /b 1
