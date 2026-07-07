-- 惠买单商户唯一店铺二维码表
-- 更新时间：2026-06-17
--
-- 前置条件：
-- 1. 已完成 CRMEB MER v3.4 基础库安装。
-- 2. 已存在商户主表 `eb_merchant`。
--
-- 执行说明：
-- 1. 本脚本只新增惠买单店铺二维码表，不修改 CRMEB 原厂表。
-- 2. 商户当前唯一维度为 `mer_id`，`scene_ext`、`branch_name_snapshot` 保留未来多门店扩展能力。

CREATE TABLE IF NOT EXISTS `eb_huimaidan_store_qrcode` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID，当前唯一维度',
  `entry_code` varchar(16) NOT NULL COMMENT '对外短码，写入scene',
  `scene_value` varchar(32) NOT NULL COMMENT '实际生成小程序码使用的scene',
  `scene_type` varchar(32) NOT NULL DEFAULT 'payment_checkout' COMMENT '场景类型：payment_checkout=买单入口',
  `page_path` varchar(255) NOT NULL DEFAULT 'pages/scan-entry/index' COMMENT '小程序页面路径，不带query',
  `qr_image_url` varchar(512) NOT NULL DEFAULT '' COMMENT '二维码图片访问地址',
  `qr_image_path` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图片相对路径',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `last_generate_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '最近生成状态：1成功 0失败',
  `last_generate_error` varchar(500) NOT NULL DEFAULT '' COMMENT '最近生成失败原因',
  `generate_version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生成版本号',
  `refresh_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '手动刷新次数',
  `last_generated_at` datetime DEFAULT NULL COMMENT '最近成功生成时间',
  `last_access_at` datetime DEFAULT NULL COMMENT '最近扫码访问时间',
  `branch_name_snapshot` varchar(64) NOT NULL DEFAULT '' COMMENT '分店名快照，预留多门店扩展',
  `scene_ext` text COMMENT '扩展字段JSON，预留未来多门店、多场景',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mer_scene` (`mer_id`, `scene_type`),
  UNIQUE KEY `uk_entry_code` (`entry_code`),
  UNIQUE KEY `uk_scene_value` (`scene_value`),
  KEY `idx_status` (`status`),
  KEY `idx_last_generate_status` (`last_generate_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单商户唯一店铺二维码';

-- 回滚说明：
-- DROP TABLE IF EXISTS `eb_huimaidan_store_qrcode`;
