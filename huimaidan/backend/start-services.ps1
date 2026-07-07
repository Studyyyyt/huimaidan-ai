# CRMEB 一键启动脚本
# 启动顺序: Nginx -> MySQL -> Redis -> Swoole -> Queue

$ErrorActionPreference = "SilentlyContinue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# 服务路径配置
$NGINX_PATH = "E:\system\phpEnv-all\phpEnv\server\nginx"
$REDIS_PATH = "E:\system\phpEnv-all\phpEnv\server\redis"
$PROJECT_PATH = Split-Path -Parent $MyInvocation.MyCommand.Path

# 颜色输出函数
function Write-Success { Write-Host "[OK] $args" -ForegroundColor Green }
function Write-Warning { Write-Host "[WARN] $args" -ForegroundColor Yellow }
function Write-Error { Write-Host "[ERROR] $args" -ForegroundColor Red }
function Write-Info { Write-Host "[INFO] $args" -ForegroundColor Cyan }

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  CRMEB Service Startup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. 检查并启动 Nginx
Write-Info "Checking Nginx..."
$nginxProcess = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
if ($nginxProcess) {
    Write-Success "Nginx is running (PID: $($nginxProcess.Id))"
} else {
    Write-Info "Starting Nginx..."
    Push-Location $NGINX_PATH
    Start-Process ".\nginx.exe" -WindowStyle Hidden
    Pop-Location
    Start-Sleep -Seconds 2
    $nginxProcess = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
    if ($nginxProcess) {
        Write-Success "Nginx started successfully"
    } else {
        Write-Error "Nginx failed to start"
    }
}

# 2. 检查并启动 MySQL
Write-Info "Checking MySQL..."
$mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue | Select-Object -First 1
if ($mysqlService -and $mysqlService.Status -eq "Running") {
    Write-Success "MySQL is running ($($mysqlService.Name))"
} else {
    Write-Info "Starting MySQL..."
    if ($mysqlService) {
        Start-Service $mysqlService.Name
        Start-Sleep -Seconds 3
        $mysqlService = Get-Service -Name $mysqlService.Name
        if ($mysqlService.Status -eq "Running") {
            Write-Success "MySQL started successfully"
        } else {
            Write-Error "MySQL failed to start"
        }
    } else {
        Write-Error "MySQL service not found"
    }
}

# 3. 检查并启动 Redis
Write-Info "Checking Redis..."
$redisProcess = Get-Process -Name "redis-server" -ErrorAction SilentlyContinue
if ($redisProcess) {
    Write-Success "Redis is running (PID: $($redisProcess.Id))"
} else {
    Write-Info "Starting Redis..."
    Push-Location $REDIS_PATH
    Start-Process ".\redis-server.exe" ".\redis.windows.conf" -WindowStyle Hidden
    Pop-Location
    Start-Sleep -Seconds 2
    $redisProcess = Get-Process -Name "redis-server" -ErrorAction SilentlyContinue
    if ($redisProcess) {
        Write-Success "Redis started successfully"
    } else {
        Write-Error "Redis failed to start"
    }
}

# 4. 检查并启动 Swoole 服务
Write-Info "Checking Swoole..."
$swooleRunning = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $proc = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "think\s+swoole") {
            $swooleRunning = $true
            Write-Success "Swoole is running (PID: $($proc.Id))"
        }
    } catch {}
}

if (-not $swooleRunning) {
    Write-Info "Starting Swoole..."
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = "php"
    $psi.Arguments = "think swoole restart"
    $psi.WorkingDirectory = $PROJECT_PATH
    $psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
    $psi.CreateNoWindow = $true
    [System.Diagnostics.Process]::Start($psi) | Out-Null
    Start-Sleep -Seconds 3
    
    $swooleRunning = $false
    Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
        $proc = $_
        try {
            $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
            if ($wmi.CommandLine -match "think\s+swoole") {
                $swooleRunning = $true
                Write-Success "Swoole started successfully"
            }
        } catch {}
    }
    if (-not $swooleRunning) {
        Write-Warning "Swoole may need more time to start"
    }
}

# 5. 检查并启动队列监听
Write-Info "Checking Queue Worker..."
$queueRunning = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $proc = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "queue:(work|listen)") {
            $queueRunning = $true
            Write-Success "Queue Worker is running (PID: $($proc.Id))"
        }
    } catch {}
}

if (-not $queueRunning) {
    Write-Info "Starting Queue Worker..."
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = "php"
    $psi.Arguments = "think queue:work --tries 2"
    $psi.WorkingDirectory = $PROJECT_PATH
    $psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
    $psi.CreateNoWindow = $true
    [System.Diagnostics.Process]::Start($psi) | Out-Null
    Start-Sleep -Seconds 2
    
    $queueRunning = $false
    Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
        $proc = $_
        try {
            $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
            if ($wmi.CommandLine -match "queue:(work|listen)") {
                $queueRunning = $true
                Write-Success "Queue Worker started successfully"
            }
        } catch {}
    }
    if (-not $queueRunning) {
        Write-Warning "Queue Worker may need more time to start"
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Startup Complete" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Info "Tip: Queue Worker must keep running continuously"
Write-Info "To stop services, run: stop-services.ps1"
Write-Info "To check status, run: check-status.ps1"
