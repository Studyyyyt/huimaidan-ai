# 惠买单 AI 推荐大脑源码交付包

## 一、交付包说明

本交付包为「惠买单 AI 推荐大脑」完整源码，包含：

- `huimaidan/backend`：ThinkPHP 6 + Swoole 后端服务
- `huimaidan/admin`：Vue2 运营后台
- `huimaidan/huimaidan-merchant`：Vue2 商户后台
- `huimaidan-uniapp`：Uni-app 微信小程序（Vue3 + TypeScript + Vite）
- `CRMEB_MER_v3.4（20260328）_139633`：原始 CRMEB 源码参考
- `data/`：客户提供的原始商户数据与数据库备份
- `deploy-wsl-php73-swoole4/`：WSL 原生部署脚本

> 如果你是第一次部署本项目，建议先阅读 [惠买单AI推荐大脑新手部署指南.md](惠买单AI推荐大脑新手部署指南.md)，里面有从零开始的详细图文步骤。

## 二、环境依赖

- Docker + Docker Compose（推荐）
- 或 WSL2 Ubuntu / 宝塔面板 / 原生 Linux
- Node.js 18+、pnpm、npm
- PHP 7.3、Swoole 4.x、swoole_loader73.so
- MySQL 5.7/8.0、Redis 5+

## 三、快速启动

### 3.1 后端服务（Docker 推荐）

```bash
cd huimaidan/backend

# 1. 复制环境模板并填写真实配置
cp .example.env .env
# 编辑 .env，至少填写以下配置：
#   APP_KEY、DATABASE 段（HOSTNAME/USERNAME/PASSWORD/DATABASE）
#   REDIS 段（REDIS_HOSTNAME/PORT/REDIS_PASSWORD）
#   AI 段（BAILIAN_APP_ID、BAILIAN_API_KEY 或其他 LLM 密钥）

# 2. 安装 PHP 依赖（如未清理 vendor 可跳过）
composer install

# 3. 启动 Docker 服务
docker-compose -f docker-compose.local.yml up -d

# 4. 导入 AI 表结构
bash scripts/apply_huimaidan_ai_sql.sh

# 5. 检查环境
bash scripts/check_huimaidan_ai_env.sh

# 6. 验证 AI 接口
php scripts/test_ai_api.php
php scripts/test_ai_rerank.php
```

Docker 启动后，后端默认监听 `http://127.0.0.1:8324`。

### 3.2 运营后台

```bash
cd huimaidan/admin
cp .env.development.example .env.development
# 编辑 .env.development，将 VUE_APP_BASE_API 指向后端地址，例如：
# VUE_APP_BASE_API=http://127.0.0.1:8324

npm install
npm run dev        # 开发
npm run build      # 生产构建
```

构建产物默认输出到 `dist/`，可通过 Nginx 或静态服务器部署。

### 3.3 商户后台

```bash
cd huimaidan/huimaidan-merchant
cp .env.development.example .env.development
# 编辑 .env.development，将 VUE_APP_BASE_API 指向后端地址

npm install
npm run dev
npm run build
```

### 3.4 小程序

```bash
cd huimaidan-uniapp
cp env/.env.example env/.env
# 编辑 env/.env，至少填写：
#   VITE_WX_APPID=你的微信小程序 AppID
#   VITE_SERVER_BASEURL=后端 API 地址（如 https://example.com）
#   VITE_SERVER_BASEURL_WEIXIN_DEVELOP/TRIAL/RELEASE 对应各环境域名

pnpm install
pnpm dev:mp       # 开发版微信小程序
pnpm build:mp     # 生产构建
```

构建完成后，用微信开发者工具导入 `huimaidan-uniapp/dist/dev/mp-weixin` 或 `dist/build/mp-weixin`。

上线前还需在微信公众平台配置 `request` 合法域名、上传小程序代码并提交审核。

## 四、WSL / 原生部署

参考 `deploy-wsl-php73-swoole4/scripts/deploy-crmeb-wsl.sh`：

```bash
bash deploy-wsl-php73-swoole4/scripts/deploy-crmeb-wsl.sh \
  --project-dir /your/project/path \
  --swoole-loader-so /path/to/swoole_loader73.so
```

部署完成后：

- Nginx 反向代理到 Swoole：`http://127.0.0.1:8324`
- 必须将 `.env` 中数据库/Redis 地址改为 `127.0.0.1`
- 必须手动导入 SQL 并启动 Supervisor 队列
- `huimaidan/backend/start_services.sh` 中的项目路径与 MySQL 密码为示例，需按实际环境修改

## 五、宝塔面板部署

1. 安装 PHP 7.3 + Swoole + swoole_loader73.so
2. 安装 MySQL 5.7/8.0、Redis、Nginx 1.18
3. 创建站点，配置反向代理到 `http://127.0.0.1:8324`
4. 设置 `public/` 和 `runtime/` 目录权限为 777
5. 导入数据库：`huimaidan/backend/docs/sql/migrations/011_惠买单_AI推荐大脑表结构.sql`
6. 编辑 `.env`，将 DB/Redis 地址改为 `127.0.0.1`
7. 启动 Swoole 与队列监听

## 六、部署方式差异说明

| 维度          | Docker（推荐）      | WSL/原生      | 宝塔          |
| ------------- | ------------------- | ------------- | ------------- |
| PHP 版本      | 7.3                 | 7.3           | 7.3           |
| Swoole 版本   | 4.5.11              | 4.8.13        | 取决于宝塔    |
| MySQL 版本    | 8.0                 | 系统版        | 5.7/8.0       |
| DB/Redis 地址 | `mysql`/`redis` | `127.0.0.1` | `127.0.0.1` |
| Nginx/HTTPS   | 无                  | 自签证书      | 需手动配置    |
| 队列          | `queue` 容器      | Supervisor    | Supervisor    |
| 可复现性      | 高                  | 低            | 中            |

**关键风险**：

- `swoole_loader73.so` 是 CRMEB 加密代码运行前提，任何非 Docker 部署必须放置与 PHP 架构匹配的 loader。
- MySQL 8.0 与 5.7 的 SQL mode 不同，宝塔/WSL 需确认 `utf8mb4` 与宽松模式配置。
- Docker 内部必须使用服务名（`mysql`/`redis`）连接，WSL/宝塔必须使用 `127.0.0.1`。
- 小程序上线前需在微信公众平台配置 request 合法域名，且微信支付 mch_id 需自行申请配置；本地测试建议开启余额支付。

## 七、是否只需修改 .env 即可部署？

**不能仅靠修改 .env 完成全部部署**，但 .env 是配置核心。除 .env 外，还需要完成以下工作：

1. **依赖安装**：首次部署必须执行 `composer install`（后端）、`npm install` / `pnpm install`（前端/小程序）。
2. **数据库导入**：必须手动导入 `011_惠买单_AI推荐大脑表结构.sql`，生产环境不要导入 `012_惠买单_AI推荐大脑开发测试数据.sql`。
3. **WSL/宝塔环境搭建**：需要独立安装 PHP 7.3 + Swoole + swoole_loader73.so、MySQL、Redis、Nginx，并配置反向代理与队列监听。
4. **启动脚本调整**：WSL 下的 `start_services.sh` 中项目路径与 MySQL 密码为示例，需按实际环境修改。
5. **微信支付配置**：微信支付 mch_id、证书等需在后台或相关配置中单独申请并配置，不能仅通过 .env 完成。
6. **模板 ID 等运行配置**：短信、微信模板 ID 等已按业务默认值硬编码在 `config/notice.php`、`config/sms.php` 中，如需更换服务商或模板，需修改对应配置文件。

简言之：Docker 方式最接近“改 .env 即可启动”；WSL/宝塔还需要完成环境搭建、Nginx 反向代理、队列监听等额外步骤。

## 八、客户数据文件

- `data/客户原始数据/商家数据.txt`：客户提供的原始商户数据
- `data/数据库备份/`：开发/测试阶段的数据库备份

导入生产环境前，请确认数据已脱敏并符合隐私合规要求。

## 九、验证命令

```bash
# 代码级验证（不构建）
bash scripts/check_huimaidan_ai_all.sh

# 代码级验证（含构建）
RUN_BUILDS=1 bash scripts/check_huimaidan_ai_all.sh

# 环境检查
CHECK_ENV=1 bash scripts/check_huimaidan_ai_all.sh

# 后端 AI 接口冒烟
php scripts/test_ai_api.php
php scripts/test_ai_rerank.php
```

## 十、注意事项

1. 上线前务必替换 `.env`、小程序 `env/.env` 中的占位符为真实配置。
2. 生产环境不要导入 `012_惠买单_AI推荐大脑开发测试数据.sql`。
3. 微信支付 mch_id 需自行申请配置；本地测试建议开启余额支付。
4. 小程序上线前需在微信公众平台配置 request 合法域名。
5. 如从 Docker 切换到 WSL/宝塔部署，必须重新检查 PHP/Swoole/swoole_loader 版本与 `.env` 连接地址。

## 十一、源码结构速览

```text
01小程序增加ai推荐功能/
├── .gitignore                  # 排除敏感/本地/运行时文件
├── README.md                   # 交付说明与部署总览
├── data/                       # 客户数据归档
│   ├── 客户原始数据/
│   └── 数据库备份/
├── huimaidan/                  # 后端+后台+商户端
│   ├── backend/                # ThinkPHP 后端
│   ├── admin/                  # Vue2 运营后台
│   └── huimaidan-merchant/     # Vue2 商户后台
├── huimaidan-uniapp/           # Uni-app 小程序
├── deploy-wsl-php73-swoole4/   # WSL 部署脚本
└── CRMEB_MER_v3.4.../          # 原始 CRMEB 源码参考
```
