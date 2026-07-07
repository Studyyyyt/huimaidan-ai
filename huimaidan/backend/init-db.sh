#!/bin/bash
# 惠买单数据库初始化脚本
# 用法：
#   1. 确保 MySQL 已启动且可连接
#   2. 把基础库 SQL 放到 docs/sql/base/crmeb_base_v1.sql
#   3. 在项目根目录执行：bash init-db.sh
#
# 可通过环境变量覆盖连接参数：
#   DB_HOST=127.0.0.1 DB_PORT=3366 DB_USER=root DB_PASS=root DB_NAME=crmeb bash init-db.sh
#
# 强制使用 Docker 容器内的 mysql 客户端：
#   USE_DOCKER=1 bash init-db.sh

set -e

# 数据库连接参数，可通过环境变量覆盖
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3366}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-root}"
DB_NAME="${DB_NAME:-crmeb}"

# Docker 容器名
DOCKER_MYSQL_CONTAINER="${DOCKER_MYSQL_CONTAINER:-huimaidan_mysql}"

# SQL 目录
BASE_DIR="docs/sql/base"
MIGRATIONS_DIR="docs/sql/migrations"

# 判断是否使用 Docker 容器内的 mysql 客户端
USE_DOCKER="${USE_DOCKER:-}"
if [ -z "${USE_DOCKER}" ]; then
    if command -v docker > /dev/null 2>&1 && docker ps --format '{{.Names}}' 2>/dev/null | grep -qx "${DOCKER_MYSQL_CONTAINER}"; then
        USE_DOCKER=1
    fi
fi

# 构造 mysql 命令前缀
if [ "${USE_DOCKER}" = "1" ]; then
    echo "使用 Docker 容器 ${DOCKER_MYSQL_CONTAINER} 内的 mysql 客户端"
    MYSQL_CMD="docker exec -i ${DOCKER_MYSQL_CONTAINER} mysql -u${DB_USER} -p${DB_PASS} --default-character-set=utf8mb4"
else
    MYSQL_CMD="mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USER} -p${DB_PASS} --default-character-set=utf8mb4"
fi

echo "================================================"
echo "惠买单数据库初始化"
echo "连接信息: ${DB_HOST}:${DB_PORT}/${DB_NAME}"
echo "================================================"

# 测试连接
echo "测试数据库连接..."
if ! ${MYSQL_CMD} -e "SELECT 1;" > /dev/null 2>&1; then
    echo "错误：无法连接到数据库 ${DB_HOST}:${DB_PORT}"
    echo "请检查："
    echo "  1. MySQL 服务是否已启动"
    echo "  2. 用户名、密码是否正确"
    if [ "${USE_DOCKER}" != "1" ]; then
        echo "  3. 如果使用 Docker，可尝试：USE_DOCKER=1 bash init-db.sh"
    fi
    exit 1
fi
echo "数据库连接成功"

# 确保目标数据库存在
if ! ${MYSQL_CMD} -e "SELECT 1;" "${DB_NAME}" > /dev/null 2>&1; then
    echo ""
    echo "数据库 ${DB_NAME} 不存在，正在创建..."
    ${MYSQL_CMD} -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo "数据库创建完成"
fi

# 导入基础库
BASE_FILE="${BASE_DIR}/crmeb_base_v1.sql"
if [ -f "${BASE_FILE}" ]; then
    echo ""
    echo "导入基础库: ${BASE_FILE}"
    ${MYSQL_CMD} "${DB_NAME}" < "${BASE_FILE}"
    echo "基础库导入完成"
else
    echo ""
    echo "警告：未找到基础库文件 ${BASE_FILE}"
    echo "如果数据库已包含 CRMEB 基础表，可忽略此警告"
fi

# 按顺序执行迁移脚本
echo ""
echo "执行增量迁移脚本..."

if [ ! -d "${MIGRATIONS_DIR}" ]; then
    echo "错误：迁移目录不存在 ${MIGRATIONS_DIR}"
    exit 1
fi

# 获取排序后的迁移文件列表（不包含子目录）
MIGRATION_FILES=$(find "${MIGRATIONS_DIR}" -maxdepth 1 -name '*.sql' | sort)

if [ -z "${MIGRATION_FILES}" ]; then
    echo "警告：未找到任何迁移脚本"
else
    for file in ${MIGRATION_FILES}; do
        filename=$(basename "${file}")
        echo "  → 执行: ${filename}"
        ${MYSQL_CMD} "${DB_NAME}" < "${file}"
    done
fi

echo ""
echo "================================================"
echo "数据库初始化完成"
echo "================================================"
