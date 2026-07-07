#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR=""
MYSQL_ROOT_PASSWORD=""
DB_NAME="crmeb"
DB_USER="crmeb"
DB_PASSWORD=""
SERVER_NAME="localhost"
SWOOLE_PORT="8325"
HTTPS_PORT="8324"
SWOOLE_VERSION="4.8.13"
SWOOLE_LOADER_SO=""

usage() {
  cat <<'USAGE'
用法:
  sudo bash deploy-crmeb-wsl.sh --project-dir PATH --mysql-root-password PASS --db-password PASS [选项]

选项:
  --project-dir PATH          WSL 内 CRMEB/ThinkPHP 项目根目录，例如 /mnt/e/...
  --mysql-root-password PASS  要设置或使用的 MySQL root TCP 登录密码
  --db-name NAME              数据库名，默认 crmeb
  --db-user USER              项目专用数据库用户，默认 crmeb
  --db-password PASS          项目专用数据库用户密码
  --server-name NAME          Nginx server_name，默认 localhost
  --swoole-port PORT          内部 Swoole 端口，默认 8325
  --https-port PORT           浏览器 HTTPS 兼容端口，默认 8324
  --swoole-version VERSION    Swoole 版本，默认 4.8.13
  --swoole-loader-so PATH     可选，PHP 7.3 Linux x64 NTS 的 swoole_loader .so
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --project-dir) PROJECT_DIR="$2"; shift 2 ;;
    --mysql-root-password) MYSQL_ROOT_PASSWORD="$2"; shift 2 ;;
    --db-name) DB_NAME="$2"; shift 2 ;;
    --db-user) DB_USER="$2"; shift 2 ;;
    --db-password) DB_PASSWORD="$2"; shift 2 ;;
    --server-name) SERVER_NAME="$2"; shift 2 ;;
    --swoole-port) SWOOLE_PORT="$2"; shift 2 ;;
    --https-port) HTTPS_PORT="$2"; shift 2 ;;
    --swoole-version) SWOOLE_VERSION="$2"; shift 2 ;;
    --swoole-loader-so) SWOOLE_LOADER_SO="$2"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "未知选项: $1" >&2; usage; exit 2 ;;
  esac
done

[[ $EUID -eq 0 ]] || { echo "请使用 root 执行: sudo bash $0 ..." >&2; exit 1; }
[[ -n "$PROJECT_DIR" && -d "$PROJECT_DIR" ]] || { echo "必须传入存在的 --project-dir" >&2; exit 1; }
[[ -f "$PROJECT_DIR/think" ]] || { echo "项目目录缺少 think 文件: $PROJECT_DIR" >&2; exit 1; }
[[ -n "$MYSQL_ROOT_PASSWORD" ]] || { echo "必须传入 --mysql-root-password" >&2; exit 1; }
[[ -n "$DB_PASSWORD" ]] || { echo "必须传入 --db-password" >&2; exit 1; }

log() { printf '\n==> %s\n' "$*"; }
svc() {
  local action="$1" service="$2"
  if command -v systemctl >/dev/null 2>&1 && systemctl list-unit-files "$service.service" >/dev/null 2>&1; then
    systemctl "$action" "$service" || service "$service" "$action"
  else
    service "$service" "$action"
  fi
}
sql_escape() { sed "s/'/''/g" <<<"$1"; }

log "安装基础软件包、PHP 7.3、MySQL、Redis、Nginx、Supervisor"
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y software-properties-common ca-certificates curl wget git unzip openssl \
  build-essential autoconf pkg-config libssl-dev libcurl4-openssl-dev zlib1g-dev
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y mysql-server redis-server nginx supervisor \
  php7.3-cli php7.3-common php7.3-dev php7.3-pear php7.3-mysql php7.3-gd \
  php7.3-bcmath php7.3-curl php7.3-mbstring php7.3-xml php7.3-zip \
  php7.3-redis php7.3-opcache php7.3-readline

if [[ -x /usr/bin/php7.3 ]]; then
  update-alternatives --set php /usr/bin/php7.3 || true
fi

log "安装 Swoole ${SWOOLE_VERSION}"
if php --ri swoole >/dev/null 2>&1 && php --ri swoole | grep -q "$SWOOLE_VERSION"; then
  echo "Swoole $SWOOLE_VERSION 已安装"
else
  pecl uninstall swoole >/dev/null 2>&1 || true
  if ! pecl install --configureoptions 'enable-openssl="yes" enable-sockets="yes" enable-mysqlnd="yes" enable-swoole-curl="yes"' "swoole-${SWOOLE_VERSION}"; then
    tmp_dir="$(mktemp -d)"
    trap 'rm -rf "$tmp_dir"' EXIT
    wget -O "$tmp_dir/swoole.tar.gz" "https://github.com/swoole/swoole-src/archive/refs/tags/v${SWOOLE_VERSION}.tar.gz"
    tar -xf "$tmp_dir/swoole.tar.gz" -C "$tmp_dir"
    cd "$tmp_dir/swoole-src-${SWOOLE_VERSION}"
    phpize7.3
    ./configure --with-php-config=/usr/bin/php-config7.3 --enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl
    make -j"$(nproc)"
    make install
  fi
  echo "extension=swoole.so" > /etc/php/7.3/mods-available/swoole.ini
  phpenmod -v 7.3 swoole
fi

if [[ -n "$SWOOLE_LOADER_SO" ]]; then
  log "安装 swoole_loader"
  [[ -f "$SWOOLE_LOADER_SO" ]] || { echo "未找到 swoole_loader 文件: $SWOOLE_LOADER_SO" >&2; exit 1; }
  ext_dir="$(php -r 'echo ini_get("extension_dir");')"
  cp "$SWOOLE_LOADER_SO" "$ext_dir/swoole_loader73.so"
  echo "extension=swoole_loader73.so" > /etc/php/7.3/mods-available/swoole_loader.ini
  phpenmod -v 7.3 swoole_loader
else
  echo "警告: 未提供 --swoole-loader-so；加密版 CRMEB 代码可能需要 swoole_loader。"
fi

log "配置 MySQL"
install -d /etc/mysql/mysql.conf.d
cat > /etc/mysql/mysql.conf.d/crmeb-sql-mode.cnf <<'MYSQLCNF'
[mysqld]
sql_mode=NO_ENGINE_SUBSTITUTION
MYSQLCNF
svc restart mysql

root_pw_sql="$(sql_escape "$MYSQL_ROOT_PASSWORD")"
db_pw_sql="$(sql_escape "$DB_PASSWORD")"
mysql_socket_cmd=(mysql -uroot)
mysql_tcp_cmd=(mysql -h127.0.0.1 -uroot -p"$MYSQL_ROOT_PASSWORD")
if "${mysql_tcp_cmd[@]}" -e "SELECT 1" >/dev/null 2>&1; then
  mysql_cmd=("${mysql_tcp_cmd[@]}")
else
  mysql_cmd=("${mysql_socket_cmd[@]}")
fi

"${mysql_cmd[@]}" <<SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${root_pw_sql}';
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '${root_pw_sql}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${db_pw_sql}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${db_pw_sql}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
svc restart mysql

log "配置 Redis"
svc restart redis-server

log "更新项目 .env"
env_file="$PROJECT_DIR/.env"
if [[ -f "$env_file" ]]; then
  cp "$env_file" "$env_file.bak.$(date +%Y%m%d%H%M%S)"
else
  touch "$env_file"
fi
upsert_env() {
  local key="$1" value="$2"
  if grep -Eq "^[[:space:]]*$key[[:space:]]*=" "$env_file"; then
    perl -0pi -e "s/^[ \\t]*\Q$key\E[ \\t]*=.*/$key = '$value'/mg" "$env_file"
  else
    printf "%s = '%s'\n" "$key" "$value" >> "$env_file"
  fi
}
upsert_env HOSTNAME 127.0.0.1
upsert_env HOSTPORT 3306
upsert_env USERNAME "$DB_USER"
upsert_env PASSWORD "$DB_PASSWORD"
upsert_env DATABASE "$DB_NAME"
upsert_env REDIS_HOSTNAME 127.0.0.1
upsert_env PORT 6379
upsert_env REDIS_PASSWORD ""
upsert_env SWOOLE_PORT "$SWOOLE_PORT"

log "配置 Nginx HTTPS 反向代理"
install -d /etc/nginx/ssl
if [[ ! -f /etc/nginx/ssl/localhost.crt || ! -f /etc/nginx/ssl/localhost.key ]]; then
  openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
    -keyout /etc/nginx/ssl/localhost.key \
    -out /etc/nginx/ssl/localhost.crt \
    -subj "/CN=${SERVER_NAME}" \
    -addext "subjectAltName=DNS:${SERVER_NAME},DNS:localhost,IP:127.0.0.1"
fi
cat > /etc/nginx/sites-available/crmeb-proxy.conf <<NGINX
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name ${SERVER_NAME} 127.0.0.1 _;
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl http2 default_server;
    listen [::]:443 ssl http2 default_server;
    listen ${HTTPS_PORT} ssl http2;
    listen [::]:${HTTPS_PORT} ssl http2;
    server_name ${SERVER_NAME} 127.0.0.1 _;

    ssl_certificate     /etc/nginx/ssl/localhost.crt;
    ssl_certificate_key /etc/nginx/ssl/localhost.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / {
        proxy_pass http://127.0.0.1:${SWOOLE_PORT};
        proxy_http_version 1.1;
        proxy_read_timeout 360s;
        proxy_redirect off;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header REMOTE-HOST \$remote_addr;
        add_header Strict-Transport-Security "max-age=31536000" always;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/crmeb-proxy.conf /etc/nginx/sites-enabled/crmeb-proxy.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t
svc restart nginx

log "配置 Supervisor 守护进程"
cat > /etc/supervisor/conf.d/crmeb.conf <<SUPERVISOR
[program:crmeb-swoole]
directory=${PROJECT_DIR}
command=/usr/bin/php think swoole restart
user=root
autostart=true
autorestart=true
startsecs=5
startretries=3
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/crmeb-swoole.log
stdout_logfile_maxbytes=20MB
stdout_logfile_backups=5

[program:crmeb-queue]
directory=${PROJECT_DIR}
command=/usr/bin/php think queue:listen --tries=2
user=root
autostart=true
autorestart=true
startsecs=5
startretries=3
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/crmeb-queue.log
stdout_logfile_maxbytes=20MB
stdout_logfile_backups=5
SUPERVISOR
svc restart supervisor || true
supervisorctl reread
supervisorctl update
supervisorctl restart crmeb-swoole crmeb-queue || true

log "验证部署结果"
php -v | head -n 1
php -m | grep -Ei 'gd|bcmath|redis|swoole|swoole_loader' || true
mysql -h127.0.0.1 -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1"
redis-cli ping
nginx -t
supervisorctl status
ss -lntp | grep -E ":80|:443|:${HTTPS_PORT}|:${SWOOLE_PORT}|:3306|:6379" || true
curl -k -I "https://${SERVER_NAME}:${HTTPS_PORT}/" || true

log "完成"
