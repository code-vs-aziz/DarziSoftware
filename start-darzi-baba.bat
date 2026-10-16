@echo off
REM Root shortcut — desktop launcher chalata hai
cd /d "%~dp0desktop"
if not exist "start-darzi-baba.bat" (
  echo desktop\start-darzi-baba.bat nahi mili.
  pause
  exit /b 1
)
call start-darzi-baba.bat
