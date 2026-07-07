#!/bin/bash

# CRMEB 服务一键启动脚本
# 适用于 WSL 环境

echo "=========================================="
echo "    CRMEB 服务一键启动脚本"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 项目根目录
PROJECT_DIR="/mnt/e/yifan/projects/huimaidan/CRMEB_MER_v3.4（20260328）_139633"
WSL_IPV4=$(hostname -I | awk '{print $1}')

# 检查服务是否运行的函数
check_service() {
    local service_name=$1
    local check_cmd=$2
    
    if eval "$check_cmd" > /dev/null 2>&1; then
        echo -e "${GREEN}[✓] $service_name 已运行${NC}"
        return 0
    else
        echo -e "${YELLOW}[!] $service_name 未运行${NC}"
        return 1
    fi
}

# 1. 检查并启动 Redis
echo "------------------------------------------"
echo "1. 检查 Redis 服务"
echo "------------------------------------------"
if ! check_service "Redis" "redis-cli ping"; then
    echo "正在启动 Redis..."
    sudo service redis-server start
    sleep 2
    if check_service "Redis" "redis-cli ping"; then
        echo -e "${GREEN}[✓] Redis 启动成功${NC}"
    else
        echo -e "${RED}[✗] Redis 启动失败，请手动检查${NC}"
    fi
fi
echo ""

# 2. 检查并启动 MySQL
echo "------------------------------------------"
echo "2. 检查 MySQL 服务"
echo "------------------------------------------"
if ! check_service "MySQL" "mysqladmin ping -u root -p'Root@2026#Reset' 2>/dev/null"; then
    echo "正在启动 MySQL..."
    sudo service mysql start
    sleep 3
    if check_service "MySQL" "mysqladmin ping -u root -p'Root@2026#Reset' 2>/dev/null"; then
        echo -e "${GREEN}[✓] MySQL 启动成功${NC}"
    else
        echo -e "${RED}[✗] MySQL 启动失败，请手动检查${NC}"
    fi
fi
echo ""

# 3. 检查并启动 Nginx
echo "------------------------------------------"
echo "3. 检查 Nginx 服务"
echo "------------------------------------------"
if ! check_service "Nginx" "systemctl is-active --quiet nginx"; then
    echo "正在启动 Nginx..."
    sudo service nginx start
    sleep 2
    if check_service "Nginx" "systemctl is-active --quiet nginx"; then
        echo -e "${GREEN}[✓] Nginx 启动成功${NC}"
    else
        echo -e "${YELLOW}[!] Nginx 可能未安装或配置问题${NC}"
        echo "    WSL 环境下 CRMEB 使用 Swoole 内置服务器，Nginx 可选"
    fi
fi
echo ""

# 4. 清除 Redis 缓存（解决配置缓存问题）
echo "------------------------------------------"
echo "4. 清除 Redis 缓存"
echo "------------------------------------------"
echo "正在清除 Redis 缓存..."
redis-cli FLUSHDB > /dev/null 2>&1
echo -e "${GREEN}[✓] Redis 缓存已清除${NC}"
echo ""

# 5. 检查并启动队列监听
echo "------------------------------------------"
echo "5. 检查队列监听"
echo "------------------------------------------"
# 5a. 默认队列（订单状态变更、推广等）
if pgrep -f "queue:work --tries 2" > /dev/null; then
    echo -e "${GREEN}[✓] 默认队列监听已运行${NC}"
else
    echo "正在启动默认队列监听..."
    cd "$PROJECT_DIR"
    nohup php think queue:work --tries 2 > /dev/null 2>&1 &
    sleep 2
    if pgrep -f "queue:work --tries 2" > /dev/null; then
        echo -e "${GREEN}[✓] 默认队列监听启动成功${NC}"
    else
        echo -e "${RED}[✗] 默认队列监听启动失败，请手动检查${NC}"
    fi
fi
# 5b. 语音播报队列
if pgrep -f "queue:work.*huimaidan_voice_push" > /dev/null; then
    echo -e "${GREEN}[✓] 语音播报队列监听已运行${NC}"
else
    echo "正在启动语音播报队列监听..."
    cd "$PROJECT_DIR"
    nohup php think queue:work --queue=huimaidan_voice_push --tries 3 > /dev/null 2>&1 &
    sleep 2
    if pgrep -f "queue:work.*huimaidan_voice_push" > /dev/null; then
        echo -e "${GREEN}[✓] 语音播报队列监听启动成功${NC}"
    else
        echo -e "${RED}[✗] 语音播报队列监听启动失败，请手动检查${NC}"
    fi
fi
echo ""

# 6. 检查并启动 Swoole 服务
echo "------------------------------------------"
echo "6. 检查 Swoole 服务"
echo "------------------------------------------"
check_swoole_running() {
    ss -tlnp 2>/dev/null | grep -q ":8324 " && curl -s -o /dev/null -w "%{http_code}" --noproxy "*" "http://127.0.0.1:8324/api/script" 2>/dev/null | grep -q "200"
}
if check_swoole_running; then
    echo -e "${GREEN}[✓] Swoole 服务已运行${NC}"
else
    echo "正在启动 Swoole 服务..."
    cd "$PROJECT_DIR"
    # 先清理可能残留的 systemd 单元
    sudo systemctl stop crmeb-swoole.service > /dev/null 2>&1 || true
    sudo systemctl disable crmeb-swoole.service > /dev/null 2>&1 || true
    # 清理旧的 pid 文件
    rm -f runtime/swoole.pid
    # 使用 php think 启动
    sudo /usr/bin/php think swoole start > /dev/null 2>&1 &
    # 等待服务就绪，最多等 15 秒
    for i in $(seq 1 15); do
        sleep 1
        if check_swoole_running; then
            echo -e "${GREEN}[✓] Swoole 服务启动成功${NC}"
            break
        fi
        if [ "$i" -eq 15 ]; then
            echo -e "${RED}[✗] Swoole 服务启动失败，请手动检查${NC}"
        fi
    done
fi
echo ""

# 7. 验证服务状态
echo "=========================================="
echo "    服务状态汇总"
echo "=========================================="
echo ""

# 检查端口
echo "端口监听状态:"
echo "------------------------------------------"
ss -tlnp 2>/dev/null | grep -E "6379|3306|8324" || echo "无法获取端口信息"
echo ""

# 检查进程
echo "关键进程状态:"
echo "------------------------------------------"
echo -n "Redis:     "
if redis-cli ping > /dev/null 2>&1; then echo -e "${GREEN}运行中${NC}"; else echo -e "${RED}未运行${NC}"; fi

echo -n "MySQL:     "
if pgrep -f "mysqld" > /dev/null; then echo -e "${GREEN}运行中${NC}"; else echo -e "${RED}未运行${NC}"; fi

echo -n "Swoole:    "
if check_swoole_running; then echo -e "${GREEN}运行中${NC}"; else echo -e "${RED}未运行${NC}"; fi

echo -n "默认队列:  "
if pgrep -f "queue:work --tries 2" > /dev/null; then echo -e "${GREEN}运行中${NC}"; else echo -e "${RED}未运行${NC}"; fi
echo -n "语音播报:  "
if pgrep -f "queue:work.*huimaidan_voice_push" > /dev/null; then echo -e "${GREEN}运行中${NC}"; else echo -e "${RED}未运行${NC}"; fi
echo ""

# 测试 API
echo "API 测试:"
echo "------------------------------------------"
echo -n "api/script: "
response=$(env -u http_proxy -u https_proxy -u HTTP_PROXY -u HTTPS_PROXY -u ALL_PROXY -u all_proxy \
    curl --noproxy "*" -s -o /dev/null -w "%{http_code}" "http://${WSL_IPV4}:8324/api/script" 2>/dev/null)
if [ "$response" = "200" ]; then
    echo -e "${GREEN}正常 (HTTP 200)${NC}"
else
    echo -e "${RED}异常 (HTTP $response)${NC}"
fi
echo ""

echo "=========================================="
echo "    访问地址"
echo "=========================================="
echo "后台管理: http://${WSL_IPV4}:8324/admin/"
echo "前端商城: http://${WSL_IPV4}:8324/"
echo ""
echo "=========================================="
echo "    启动完成"
echo "=========================================="
