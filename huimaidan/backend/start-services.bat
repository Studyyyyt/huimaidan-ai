@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ========================================
echo   CRMEB Service Startup Script
echo ========================================
echo.

set "NGINX_PATH=E:\system\phpEnv-all\phpEnv\server\nginx"
set "REDIS_PATH=E:\system\phpEnv-all\phpEnv\server\redis"
set "PROJECT_PATH=%~dp0"

:: 1. Check and start Nginx
echo [INFO] Checking Nginx...
tasklist /FI "IMAGENAME eq nginx.exe" 2>NUL | find /I "nginx.exe" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Nginx is already running
) else (
    echo [INFO] Starting Nginx...
    pushd "%NGINX_PATH%"
    start /B nginx.exe
    popd
    timeout /t 2 /nobreak >nul
    tasklist /FI "IMAGENAME eq nginx.exe" 2>NUL | find /I "nginx.exe" >NUL
    if %ERRORLEVEL% == 0 (
        echo [OK] Nginx started successfully
    ) else (
        echo [ERROR] Nginx failed to start
    )
)

:: 2. Check and start MySQL
echo [INFO] Checking MySQL...
sc query MySQL80 2>NUL | find "RUNNING" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] MySQL is already running
) else (
    echo [INFO] Starting MySQL...
    net start MySQL80
    timeout /t 3 /nobreak >nul
    sc query MySQL80 2>NUL | find "RUNNING" >NUL
    if %ERRORLEVEL% == 0 (
        echo [OK] MySQL started successfully
    ) else (
        echo [ERROR] MySQL failed to start
    )
)

:: 3. Check and start Redis
echo [INFO] Checking Redis...
tasklist /FI "IMAGENAME eq redis-server.exe" 2>NUL | find /I "redis-server.exe" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Redis is already running
) else (
    echo [INFO] Starting Redis...
    pushd "%REDIS_PATH%"
    start /B redis-server.exe redis.windows.conf
    popd
    timeout /t 2 /nobreak >nul
    tasklist /FI "IMAGENAME eq redis-server.exe" 2>NUL | find /I "redis-server.exe" >NUL
    if %ERRORLEVEL% == 0 (
        echo [OK] Redis started successfully
    ) else (
        echo [ERROR] Redis failed to start
    )
)

:: 4. Check and start Swoole
echo [INFO] Checking Swoole...
netstat -ano | findstr ":9501" | findstr "LISTENING" >NUL
if %ERRORLEVEL% == 0 (
    echo [OK] Swoole is already running on port 9501
) else (
    echo [INFO] Starting Swoole...
    pushd "%PROJECT_PATH%"
    start /B php think swoole restart
    popd
    timeout /t 5 /nobreak >nul
    netstat -ano | findstr ":9501" | findstr "LISTENING" >NUL
    if %ERRORLEVEL% == 0 (
        echo [OK] Swoole started successfully
    ) else (
        echo [WARN] Swoole may need more time to start
    )
)

:: 5. Check and start Queue Worker
echo [INFO] Checking Queue Worker...
set "queueFound=0"
for /f "tokens=2" %%a in ('wmic process where "name='php.exe'" get ProcessId^,CommandLine 2^>nul ^| findstr "queue:work"') do (
    set "queueFound=1"
)
if "%queueFound%"=="1" (
    echo [OK] Queue Worker is already running
) else (
    echo [INFO] Starting Queue Worker...
    pushd "%PROJECT_PATH%"
    start /B php think queue:work --tries 2
    popd
    timeout /t 2 /nobreak >nul
    echo [OK] Queue Worker started
)

echo.
echo ========================================
echo   Startup Complete
echo ========================================
echo.
echo [INFO] Tip: Queue Worker must keep running continuously
echo [INFO] To stop services, run: stop-services.bat
echo [INFO] To check status, run: check-status.bat
echo.
pause
