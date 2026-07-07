@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ========================================
echo   CRMEB Service Status
echo ========================================
echo.

:: Nginx
tasklist /FI "IMAGENAME eq nginx.exe" 2>NUL | find /I "nginx.exe" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Nginx:    Running
) else (
    echo [ERROR] Nginx:    Not Running
)

:: MySQL
sc query MySQL80 2>NUL | find "RUNNING" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] MySQL:    Running
) else (
    echo [ERROR] MySQL:    Not Running
)

:: Redis
tasklist /FI "IMAGENAME eq redis-server.exe" 2>NUL | find /I "redis-server.exe" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Redis:    Running
) else (
    echo [ERROR] Redis:    Not Running
)

:: Swoole
netstat -ano | findstr ":9501" | findstr "LISTENING" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Swoole:   Running on port 9501
) else (
    echo [ERROR] Swoole:   Not Running
)

:: Queue Worker
set "queueFound=0"
for /f "tokens=2" %%a in ('wmic process where "name='php.exe'" get ProcessId^,CommandLine 2^>nul ^| findstr "queue:work"') do (
    set "queueFound=1"
    echo [OK] Queue:    Running ^(PID: %%a^)
)
if "%queueFound%"=="0" (
    echo [ERROR] Queue:    Not Running
)

echo.
echo ========================================
echo.
pause
