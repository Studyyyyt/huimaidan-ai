# CRMEB 一键停止脚本
# 停止顺序: Queue -> Swoole -> Redis -> Nginx

$ErrorActionPreference = "SilentlyContinue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

function Write-Success { Write-Host "[OK] $args" -ForegroundColor Green }
function Write-Warning { Write-Host "[WARN] $args" -ForegroundColor Yellow }
function Write-Error { Write-Host "[ERROR] $args" -ForegroundColor Red }
function Write-Info { Write-Host "[INFO] $args" -ForegroundColor Cyan }

Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  CRMEB Service Stop Script" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

# 1. 停止队列监听
Write-Info "Stopping Queue Worker..."
$stopped = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $proc = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($proc.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "queue:(work|listen)") {
            Stop-Process -Id $proc.Id -Force
            $stopped = $true
        }
    } catch {}
}
if ($stopped) { Write-Success "Queue Worker stopped" }
else { Write-Warning "Queue Worker not found" }

# 2. 停止 Swoole 服务
Write-Info "Stopping Swoole..."
$PROJECT_PATH = Split-Path -Parent $MyInvocation.MyCommand.Path
$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = "php"
$psi.Arguments = "think swoole stop"
$psi.WorkingDirectory = $PROJECT_PATH
$psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
$psi.CreateNoWindow = $true
$psi.RedirectStandardOutput = $true
$proc = [System.Diagnostics.Process]::Start($psi)
$proc.WaitForExit(5000)

$stopped = $false
Get-Process -Name "php*" -ErrorAction SilentlyContinue | ForEach-Object {
    $p = $_
    try {
        $wmi = Get-CimInstance Win32_Process -Filter "ProcessId=$($p.Id)" -ErrorAction Stop
        if ($wmi.CommandLine -match "think\s+swoole") {
            Stop-Process -Id $p.Id -Force
            $stopped = $true
        }
    } catch {}
}
Write-Success "Swoole stopped"

# 3. 停止 Redis
Write-Info "Stopping Redis..."
$redisProcess = Get-Process -Name "redis-server" -ErrorAction SilentlyContinue
if ($redisProcess) {
    Stop-Process -Name "redis-server" -Force
    Write-Success "Redis stopped"
} else {
    Write-Warning "Redis not found"
}

# 4. 停止 Nginx
Write-Info "Stopping Nginx..."
$nginxProcess = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
if ($nginxProcess) {
    Stop-Process -Name "nginx" -Force
    Write-Success "Nginx stopped"
} else {
    Write-Warning "Nginx not found"
}

# 5. MySQL 提示
Write-Host ""
Write-Info "MySQL runs as a Windows service and will not be stopped automatically"
Write-Info "To stop MySQL, use services.msc"

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  All services stopped" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
