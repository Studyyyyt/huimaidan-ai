# 惠买单数据库初始化与迁移说明

本目录用于统一管理项目数据库的初始化和增量变更。

## 目录结构

```
docs/sql/
├── base/                                # 完整基础库（已包含 CRMEB + 惠买单历史变更）
│   ├── README.md
│   └── crmeb_base_v1.sql                # 当前可用数据库的完整导出
├── migrations/                          # 惠买单各功能模块的增量 SQL（可重复执行）
│   ├── 001_惠买单_后端菜单权限.sql
│   ├── 002_惠买单_商户端菜单权限.sql
│   ├── 003_惠买单_小程序商户展示扩展表.sql
│   ├── 004_惠买单_店铺标签与富文本简介.sql
│   ├── 005_惠买单_店铺浏览历史表.sql
│   ├── 006_惠买单_小程序优惠抵扣配置.sql
│   ├── 007_惠买单_邀请码功能配置.sql
│   ├── 008_惠买单_商户唯一店铺二维码表.sql
│   ├── 009_惠买单_店铺二维码菜单权限.sql
│   ├── 010_voice_device_tables.sql
│   ├── 011_惠买单_AI推荐大脑表结构.sql
│   ├── 012_惠买单_AI推荐大脑开发测试数据.sql
│   └── historical/                      # 已包含在 base 中的历史变更，仅作留档参考
│       ├── 001_惠买单_一期后端数据库变更.sql
│       ├── 002_惠买单_二期后端数据库变更.sql
│       └── 003_惠买单_三期后端数据库变更.sql
└── README.md
```

## 基础库说明

`base/crmeb_base_v1.sql` 是**当前可用数据库的完整导出**，已包含：

- CRMEB 官方基础表和初始数据
- 惠买单一期、二期、三期的数据库变更（垫资池、商家折扣、订单扩展字段等）
- 惠买单各功能模块表（商户展示扩展、优惠抵扣、邀请码、二维码、语音设备、AI 推荐大脑等）

因此在新环境初始化时，**只需要导入 `base/crmeb_base_v1.sql`，再执行 `migrations/` 下的增量 SQL 即可**。

## 使用方式

### 方式一：命令行自动初始化（推荐）

```bash
cd /path/to/huimaidan/backend
bash init-db.sh
```

脚本会：

1. 导入 `docs/sql/base/crmeb_base_v1.sql`
2. 按编号顺序执行 `docs/sql/migrations/` 下的所有增量 SQL（不包含 `historical/` 子目录）

可通过环境变量覆盖连接参数：

```bash
DB_HOST=127.0.0.1 DB_PORT=3366 DB_USER=root DB_PASS=root DB_NAME=crmeb bash init-db.sh
```

### 方式二：可视化工具手动执行

1. 用 Navicat / DataGrip / DBeaver 连接数据库：
   - 主机：`127.0.0.1`
   - 端口：`3366`（Docker 映射端口）
   - 用户名：`root`
   - 密码：`root`
   - 数据库：`crmeb`

2. 执行 `docs/sql/base/crmeb_base_v1.sql`
3. 按编号顺序执行 `docs/sql/migrations/` 下的所有 SQL（跳过 `historical/`）

## 新增功能时如何维护

每次新增功能需要修改数据库时：

1. 在 `migrations/` 下新建文件，编号递增，例如：
   ```
   013_惠买单_新功能表结构.sql
   014_惠买单_新功能测试数据.sql
   ```
2. SQL 文件中尽量使用 `CREATE TABLE IF NOT EXISTS` 和 `INSERT ... ON DUPLICATE KEY UPDATE`，保证可重复执行
3. 不要修改已存在的迁移文件，保持历史可追溯
4. 如果新增功能包含 `ALTER TABLE`，需要确保在已导入 base 的环境上也能重复执行（建议使用 `IF NOT EXISTS` 或先判断列是否存在）

## 更新基础库的时机

当数据库结构发生较大变化（例如上线前整合了多个新功能），可以重新导出当前数据库覆盖 `base/crmeb_base_v1.sql`，并将已合并的迁移文件移入 `migrations/historical/`。

重新导出命令（在 Docker 容器中执行）：

```bash
docker exec huimaidan_mysql mysqldump -uroot -proot \
  --default-character-set=utf8mb4 \
  --single-transaction \
  --routines \
  --triggers \
  --hex-blob \
  --set-gtid-purged=OFF \
  crmeb > docs/sql/base/crmeb_base_v1.sql
```

## 注意事项

- 所有 SQL 文件必须使用 **utf8mb4** 字符集
- 基础库文件通常只在新环境初始化时使用一次
- `migrations/` 下的文件可以反复执行，适合持续集成和多人协作
- `migrations/historical/` 中的 SQL 已包含在 base 中，仅用于追溯历史，不要直接执行
- 生产环境部署前，建议在测试库先验证所有 SQL 能正常执行
