# CRMEB 服务状态检查脚本

$ErrorActionPreference = "SilentlyContinue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

function Write-Success { Write-Host "[OK] $args" -ForegroundColor Green }
function Write-Warning { Write-Host "[WARN] $args" -ForegroundColor Yellow }
function Write-Error { Write-Host "[ERROR] $args" -ForegroundColor Red }
function Write-Info { Write-Host "[INFO] $args" -ForegroundColor Cyan }

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  CRMEB Service Status" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Nginx
$nginxProcess = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
if ($nginxProcess) {
    Write-Success "Nginx:    Running (PID: $($nginxProcess.Id))"
} else {
    Write-Error "Nginx:    Not Running"
}

# MySQL
$mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue | Select-Object -First 1
if ($mysqlService -and $mysqlService.Status -eq "Running") {
    Write-Success "MySQL:    Running ($($mysqlService.Name))"
} else {
    Write-Error "MySQL:    Not Running"
}

# Redis
$redisProcess = Get-Process -Name "redis-server" -ErrorAction SilentlyContinue
if ($redisProcess) {
    Write-Success "Redis:    Running (PID: $($redisProcess.Id))"
} else {
    Write-Error "Redis:    Not Running"
}

# Swoole
$swooleRunning = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $proc = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "think\s+swoole") {
            $swooleRunning = $true
            Write-Success "Swoole:   Running (PID: $($proc.Id))"
        }
    } catch {}
}
if (-not $swooleRunning) {
    Write-Error "Swoole:   Not Running"
}

# Queue
$queueRunning = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $proc = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "queue:(work|listen)") {
            $queueRunning = $true
            Write-Success "Queue:    Running (PID: $($proc.Id))"
        }
    } catch {}
}
if (-not $queueRunning) {
    Write-Error "Queue:    Not Running"
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
