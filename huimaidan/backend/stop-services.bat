@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ========================================
echo   CRMEB Service Stop Script
echo ========================================
echo.

set "PROJECT_PATH=%~dp0"

:: 1. Stop Queue Worker
echo [INFO] Stopping Queue Worker...
set "queueFound=0"
for /f "tokens=2" %%a in ('wmic process where "name='php.exe'" get ProcessId^,CommandLine 2^>nul ^| findstr "queue:work"') do (
    taskkill /PID %%a /F >nul 2>&1
    set "queueFound=1"
)
if "%queueFound%"=="1" (
    echo [OK] Queue Worker stopped
) else (
    echo [WARN] Queue Worker not found
)

:: 2. Stop Swoole
echo [INFO] Stopping Swoole...
pushd "%PROJECT_PATH%"
php think swoole stop >nul 2>&1
popd
timeout /t 2 /nobreak >nul
netstat -ano | findstr ":9501" | findstr "LISTENING" >NUL
if %ERRORLEVEL% == 0 (
    echo [WARN] Swoole may still be running
) else (
    echo [OK] Swoole stopped
)

:: 3. Stop Redis
echo [INFO] Stopping Redis...
tasklist /FI "IMAGENAME eq redis-server.exe" 2>NUL | find /I "redis-server.exe" >NUL
if %ERRORLEVEL% == 0 (
    taskkill /IM redis-server.exe /F >nul 2>&1
    echo [OK] Redis stopped
) else (
    echo [WARN] Redis not found
)

:: 4. Stop Nginx
echo [INFO] Stopping Nginx...
tasklist /FI "IMAGENAME eq nginx.exe" 2>NUL | find /I "nginx.exe" >NUL
if %ERRORLEVEL% == 0 (
    taskkill /IM nginx.exe /F >nul 2>&1
    echo [OK] Nginx stopped
) else (
    echo [WARN] Nginx not found
)

:: 5. MySQL info
echo.
echo [INFO] MySQL runs as a Windows service
echo [INFO] To stop MySQL, run: net stop MySQL80

echo.
echo ========================================
echo   All services stopped
echo ========================================
echo.
pause
