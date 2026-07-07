# 惠买单 AI 推荐大脑 新手部署指南

> 适用对象：第一次接触本项目的开发者或运维人员
> 目标：帮助你从零开始，把「惠买单 AI 推荐大脑」完整跑起来。

---

## 一、项目是什么

「惠买单 AI 推荐大脑」是一个本地生活推荐小程序，核心能力是根据用户位置、意图和商户标签，用 AI 动态排序并推荐附近商户。

交付包主要包含 4 个可运行系统：

| 系统          | 技术栈                      | 路径                             | 作用                                      |
| ------------- | --------------------------- | -------------------------------- | ----------------------------------------- |
| 后端 API 服务 | ThinkPHP 6 + Swoole         | `huimaidan/backend`            | 提供所有业务接口、AI 推荐接口、支付回调等 |
| 运营后台      | Vue2 + Element UI           | `huimaidan/admin`              | 平台管理员使用，管理商户、订单、AI 配置等 |
| 商户后台      | Vue2 + Element UI           | `huimaidan/huimaidan-merchant` | 商户使用，管理店铺、优惠、核销等          |
| 微信小程序    | Uni-app + Vue3 + TypeScript | `huimaidan-uniapp`             | 用户端小程序，推荐、搜索、下单、支付      |

此外还包含：

- `CRMEB_MER_v3.4（20260328）_139633/`：原始 CRMEB 源码参考（本项目基于它二次开发）
- `deploy-wsl-php73-swoole4/`：WSL / 原生 Linux 部署脚本
- `data/`：客户原始商户数据与数据库备份

## 二、部署前准备

### 2.1 你需要一台什么电脑/服务器

| 部署方式       | 推荐环境                   | 最低配置          |
| -------------- | -------------------------- | ----------------- |
| Docker（推荐） | macOS / Windows 11 / Linux | 8G 内存、30G 磁盘 |
| 宝塔面板       | 云服务器（CentOS/Ubuntu）  | 2核4G、50G 磁盘   |
| WSL2           | Windows 10/11              | 8G 内存、30G 磁盘 |

> 如果你用 Apple Silicon Mac（M1/M2/M3）跑 Docker，需要开启 Rosetta，因为后端 PHP 容器使用 `linux/amd64` 以兼容 `swoole_loader73.so`。

### 2.2 需要提前申请的账号/资料

部署小程序和上线必须准备以下资料：

1. **微信小程序账号**：在微信公众平台注册小程序，获取 `AppID` 和 `AppSecret`。
2. **后端服务器/域名**：需要一台可公网访问的服务器 + 已备案域名（国内小程序要求）。
3. **HTTPS 证书**：小程序只支持 HTTPS，需要 SSL 证书（宝塔可一键申请 Let's Encrypt）。
4. **大模型 API Key**（AI 推荐必需）：
   - 阿里云百炼 `AppID` + `API Key`（推荐）
   - 或 DeepSeek `API Key`
   - 或 Claude `API Key`
5. **微信支付商户号**（可选，如需微信支付）：`mch_id`、API 密钥、证书文件。
6. **短信服务**（可选，如需短信验证码）：阿里云短信或其他服务商。

### 2.3 需要会用的工具

- 命令行（终端 / PowerShell / CMD）
- 一个代码编辑器（VS Code 即可）
- Git（用于版本管理）
- Docker Desktop（如果用 Docker 部署）
- 微信开发者工具（用于预览和上传小程序）

---

## 三、部署方式怎么选

| 方式                     | 适合人群               | 优点                           | 缺点                                  |
| ------------------------ | ---------------------- | ------------------------------ | ------------------------------------- |
| **Docker（推荐）** | 开发测试、快速验证     | 一键启动、环境一致、不容易出错 | 需要 Docker、Apple Silicon 需 Rosetta |
| **宝塔面板**       | 生产服务器、有运维经验 | 可视化操作、接近生产环境       | 需要手动配置 PHP/Swoole/loader        |
| **WSL2**           | Windows 本地开发       | 接近 Linux 生产环境            | 安装步骤多、容易遇到系统依赖问题      |

**新手建议**：先用 Docker 把项目跑起来，熟悉各模块后再考虑宝塔/WSL 生产部署。

---

## 四、方式一：Docker 部署（推荐）

### 4.1 安装 Docker

1. 下载并安装 [Docker Desktop](https://www.docker.com/products/docker-desktop/)。
2. macOS 用户：打开 Docker Desktop → Settings → General → 勾选 **Use Rosetta for x86/amd64 emulation**。
3. 验证安装：

```bash
docker --version
docker-compose --version
```

如果能看到版本号，说明安装成功。

### 4.2 部署后端服务

#### 步骤 1：进入后端目录

```bash
cd huimaidan/backend
```

#### 步骤 2：复制环境模板

```bash
cp .example.env .env
```

然后用编辑器打开 `.env`，至少填写以下配置（把 `#...#` 占位符换成真实值）：

```ini
APP_KEY = '你的随机字符串，用于加密，长度随意但建议16位以上'

[DATABASE]
HOSTNAME = 'mysql'      # Docker 内部用服务名，不要改
HOSTPORT = '3306'       # 容器内部端口，不要改
USERNAME = 'crmeb'      # 和 docker-compose.local.yml 里一致
PASSWORD = 'root'       # 和 docker-compose.local.yml 里一致
DATABASE = 'crmeb'      # 和 docker-compose.local.yml 里一致
PREFIX = 'eb_'          # 表前缀，按需修改

[REDIS]
REDIS_HOSTNAME = 'redis'  # Docker 内部用服务名，不要改
PORT = '6379'             # 容器内部端口，不要改
REDIS_PASSWORD = 'root'   # 和 docker-compose.local.yml 里一致
SELECT = '0'

# AI 配置（以百炼为例）
HUIMAIDAN_AI_ENABLED = true
HUIMAIDAN_AI_LLM_DRIVER = bailian
BAILIAN_APP_ID = '你的百炼 AppID'
BAILIAN_API_KEY = '你的百炼 API Key'
```

> 如果你用 DeepSeek 或 Claude，把 `HUIMAIDAN_AI_LLM_DRIVER` 改成对应值，并填写对应 API Key。

#### 步骤 3：安装 PHP 依赖

如果你的 `vendor/` 目录已经存在且完整，可以跳过这一步。否则执行：

```bash
composer install
```

> 如果本机没有 PHP 7.3 环境，这一步也可以在 Docker 容器内完成，见下文常见问题。

#### 步骤 4：启动 Docker 服务

```bash
docker-compose -f docker-compose.local.yml up -d
```

第一次启动会拉取镜像并构建 PHP 容器，可能需要 5-15 分钟，请耐心等待。

#### 步骤 5：查看容器是否启动成功

```bash
docker-compose -f docker-compose.local.yml ps
```

正常应该看到 4 个容器都是 `Up` 状态：

- `huimaidan_mysql`
- `huimaidan_redis`
- `huimaidan_php`
- `huimaidan_queue`

#### 步骤 6：导入 AI 表结构

```bash
bash scripts/apply_huimaidan_ai_sql.sh
```

如果要同时导入测试数据（仅本地测试用），执行：

```bash
WITH_TEST_DATA=1 bash scripts/apply_huimaidan_ai_sql.sh
```

> 生产环境**不要**导入测试数据。

#### 步骤 7：检查环境配置

```bash
bash scripts/check_huimaidan_ai_env.sh
```

如果看到 `passed`，说明配置正确。

#### 步骤 8：验证 AI 接口

```bash
php scripts/test_ai_api.php
php scripts/test_ai_rerank.php
```

如果返回正常 JSON，说明后端 AI 能力已经可用。

#### 步骤 9：浏览器访问后端

打开浏览器访问：

```
http://127.0.0.1:8324
```

如果能看到 CRMEB 默认页面或接口响应，说明后端部署成功。

### 4.3 部署运营后台

#### 步骤 1：进入目录并安装依赖

```bash
cd huimaidan/admin
cp .env.development.example .env.development
npm install
```

#### 步骤 2：修改 API 地址

编辑 `.env.development`：

```ini
VUE_APP_BASE_API=http://127.0.0.1:8324
```

#### 步骤 3：启动开发环境

```bash
npm run dev
```

控制台会显示访问地址，通常是 `http://localhost:9527` 或类似地址。

#### 步骤 4：生产构建

如果要部署到服务器：

```bash
npm run build
```

构建产物在 `dist/` 目录，可以用 Nginx 等静态服务器部署。

### 4.4 部署商户后台

与运营后台步骤完全相同，只是目录不同：

```bash
cd huimaidan/huimaidan-merchant
cp .env.development.example .env.development
# 编辑 .env.development，填写后端地址
npm install
npm run dev
```

### 4.5 部署微信小程序

#### 步骤 1：进入目录并安装依赖

```bash
cd huimaidan-uniapp
cp env/.env.example env/.env
pnpm install
```

#### 步骤 2：填写小程序配置

编辑 `env/.env`：

```ini
VITE_WX_APPID = '你的微信小程序 AppID'
VITE_SERVER_BASEURL = 'https://你的后端域名'
VITE_SERVER_BASEURL_WEIXIN_DEVELOP = 'https://你的后端域名'
VITE_SERVER_BASEURL_WEIXIN_TRIAL = 'https://你的后端域名'
VITE_SERVER_BASEURL_WEIXIN_RELEASE = 'https://你的后端域名'
```

> 本地 Docker 测试时，可以把域名写成 `http://127.0.0.1:8324`，但微信小程序真机调试要求 HTTPS，所以上线前必须换成域名。

#### 步骤 3：构建微信小程序

```bash
pnpm dev:mp
```

#### 步骤 4：用微信开发者工具导入

打开微信开发者工具 → 导入项目 → 选择：

```
huimaidan-uniapp/dist/dev/mp-weixin
```

填入你的小程序 AppID，即可预览。

---

## 五、方式二：宝塔面板部署

宝塔适合部署到云服务器。整体思路是：

1. 在宝塔安装 PHP 7.3 + Swoole + swoole_loader73.so
2. 安装 MySQL 5.7/8.0、Redis、Nginx
3. 创建站点，Nginx 反向代理到 Swoole 端口 `8324`
4. 设置目录权限
5. 导入数据库
6. 启动 Swoole 和队列监听

### 5.1 安装环境

1. 登录宝塔面板。
2. 安装以下软件：
   - PHP 7.3（安装扩展：swoole、fileinfo、openssl、gd、redis、mbstring、zip、bcmath）
   - MySQL 5.7 或 8.0
   - Redis
   - Nginx 1.18+
3. 将 `swoole_loader73.so` 上传到 PHP 扩展目录，并在 `php.ini` 中启用：

```ini
extension=swoole_loader73.so
```

> `swoole_loader73.so` 必须和 PHP 架构匹配（x86_64 / ARM64），否则后端会 500。

### 5.2 创建站点并配置反向代理

1. 宝塔 → 网站 → 添加站点，域名填写你的域名。
2. 站点根目录指向 `huimaidan/backend/public`。
3. 在站点设置里找到「反向代理」→ 添加反向代理：
   - 目标 URL：`http://127.0.0.1:8324`
   - 发送域名：`$host`
4. 配置 SSL 证书（必须，小程序要求 HTTPS）。

### 5.3 导入数据库

1. 宝塔 → 数据库 → 创建数据库（如 `crmeb`）。
2. 导入 `huimaidan/backend/docs/sql/migrations/011_惠买单_AI推荐大脑表结构.sql`。
3. 如需测试数据，再导入 `012_惠买单_AI推荐大脑开发测试数据.sql`（生产环境不要导入）。

### 5.4 配置 .env

进入 `huimaidan/backend`，复制 `.example.env` 为 `.env`，并修改：

```ini
[DATABASE]
HOSTNAME = '127.0.0.1'    # 宝塔用 127.0.0.1，不是 mysql
HOSTPORT = '3306'
USERNAME = '你的数据库用户名'
PASSWORD = '你的数据库密码'
DATABASE = 'crmeb'

[REDIS]
REDIS_HOSTNAME = '127.0.0.1'  # 宝塔用 127.0.0.1，不是 redis
PORT = '6379'
REDIS_PASSWORD = '你的 Redis 密码'
```

### 5.5 设置目录权限

在宝塔文件管理器或 SSH 中执行：

```bash
chmod -R 777 /www/wwwroot/你的站点目录/huimaidan/backend/public
chmod -R 777 /www/wwwroot/你的站点目录/huimaidan/backend/runtime
```

### 5.6 启动 Swoole 和队列

在 `huimaidan/backend` 目录下执行：

```bash
php think swoole start
```

另开一个终端启动队列：

```bash
php think queue:listen --tries=2
```

生产环境建议用 Supervisor 守护这两个进程。

### 5.7 部署前端和小程序

运营后台、商户后台、小程序的构建步骤与 Docker 方式相同，只是 `.env` 里的后端地址要改成你的域名。

---

## 六、方式三：WSL2 部署

WSL2 适合 Windows 用户本地开发。主要步骤：

1. 启用 WSL2 并安装 Ubuntu。
2. 运行部署脚本：

```bash
bash deploy-wsl-php73-swoole4/scripts/deploy-crmeb-wsl.sh \
  --project-dir /your/project/path \
  --swoole-loader-so /path/to/swoole_loader73.so
```

1. 部署完成后，修改 `.env` 中数据库/Redis 地址为 `127.0.0.1`。
2. 手动导入 SQL。
3. 启动 Supervisor 队列。

> `huimaidan/backend/start_services.sh` 中的项目路径和 MySQL 密码是示例，必须按你的实际环境修改。

---

## 七、部署后验证清单

每完成一种部署方式，请按以下清单检查：

- [ ] 后端 `http://域名或IP:8324` 可以访问
- [ ] `bash scripts/check_huimaidan_ai_env.sh` 检查通过
- [ ] `php scripts/test_ai_api.php` 返回正常结果
- [ ] `php scripts/test_ai_rerank.php` 返回正常结果
- [ ] 运营后台可以登录
- [ ] 商户后台可以登录
- [ ] 微信小程序可以在开发者工具中打开首页
- [ ] 小程序能正常请求后端接口（无 500/404）

---

## 八、常见问题

### Q1：Docker 启动时提示端口被占用

检查 `3366`、`6399`、`8324` 是否被其他程序占用，或在 `docker-compose.local.yml` 中改成其他端口。

### Q2：后端提示 `swoole_loader73.so` 加载失败

非 Docker 部署时，必须放置与 PHP 架构（x86_64 或 ARM64）匹配的 `swoole_loader73.so`，并在 `php.ini` 中正确加载。

### Q3：小程序提示「请求失败」或「不在合法域名列表」

1. 检查 `env/.env` 中的 `VITE_SERVER_BASEURL` 是否正确。
2. 在微信公众平台 → 开发管理 → 开发设置 → 服务器域名中，添加你的后端域名到 `request` 合法域名。

### Q4：微信支付失败

本地测试建议开启余额支付。正式上线前需要：

1. 申请微信支付商户号。
2. 在后台配置 `mch_id`、API 密钥、证书。
3. 小程序后台绑定商户号。

### Q5：AI 推荐没有返回结果

1. 检查 `.env` 中 `BAILIAN_APP_ID` 和 `BAILIAN_API_KEY` 是否填写正确。
2. 检查百炼应用是否已发布、是否有余额。
3. 查看后端日志 `runtime/log/` 中的错误信息。

### Q6：运营后台/商户后台 build 后空白

检查 `.env.production` 或构建时指定的 `VUE_APP_BASE_API` 是否指向正确的后端地址。

---

## 九、上线前必须做的事

1. 替换所有 `.env` 中的占位符为真实配置。
2. 生产环境不要导入 `012_惠买单_AI推荐大脑开发测试数据.sql`。
3. 配置微信支付（如需）。
4. 配置短信服务（如需）。
5. 在微信公众平台配置小程序服务器域名、业务域名。
6. 提交小程序审核并发布。
7. 配置 HTTPS 和 SSL 证书。
8. 配置服务器防火墙，只开放必要端口。

---

## 十、联系与支持

如果在部署过程中遇到本指南未覆盖的问题，建议：

1. 先查看后端日志 `huimaidan/backend/runtime/log/`。
2. 查看小程序开发者工具控制台 Network 面板。
3. 核对 `.env` 和 `env/.env` 中的配置是否与真实环境一致。

祝你部署顺利！
