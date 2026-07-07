# 基础库目录

本目录用于存放惠买单完整基础库 SQL 文件。

## 文件说明

- `crmeb_base_v1.sql`：当前可用数据库的完整导出
  - 包含 CRMEB 官方基础表和初始数据
  - 包含惠买单一期、二期、三期的数据库变更
  - 包含惠买单各功能模块表（商户展示扩展、优惠抵扣、邀请码、二维码、语音设备、AI 推荐大脑等）
  - 文件大小约 5.2 MB，共 182 张表

## 来源

此文件通过以下命令从当前 Docker MySQL 容器导出：

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

## 验证

已使用临时数据库 `crmeb_test_import` 完成导入验证，确认 178 张表均可正常创建。

## 使用

在新服务器部署时，先执行本文件，再执行 `../migrations/` 下的增量 SQL：

```bash
cd huimaidan/backend
bash init-db.sh
```

## 字符集

基础库和增量迁移都必须使用 **utf8mb4**，否则会出现中文乱码。
