-- =====================================================
-- 惠买单 - 收款语音播报功能 数据库脚本
-- 生成日期：2026-06-16
-- 说明：创建语音播报设备表和播报日志表
-- =====================================================

-- 1. 语音播报设备表
CREATE TABLE IF NOT EXISTS `eb_merchant_voice_device` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '设备ID',
  `mer_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户ID',
  `device_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '设备SN序列号（推送播报唯一凭证）',
  `device_name` varchar(64) NOT NULL DEFAULT '' COMMENT '设备名称（用户自定义）',
  `device_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '设备类型 1=三木森语音播报器',
  `bind_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '绑定状态 0=未绑定 1=已绑定',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1=启用 0=禁用',
  `last_push_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后播报时间',
  `total_push_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '累计播报次数',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除 0=否 1=是',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_mer_id` (`mer_id`),
  KEY `idx_device_sn` (`device_sn`),
  KEY `idx_mer_status` (`mer_id`, `status`, `is_del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商户语音播报设备表';

-- 2. 语音播报推送日志表
CREATE TABLE IF NOT EXISTS `eb_voice_push_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `mer_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户ID',
  `device_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '设备ID',
  `device_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '设备SN',
  `order_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联订单ID',
  `order_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `push_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '播报类型 1=收款播报 2=测试播报',
  `push_content` varchar(255) NOT NULL DEFAULT '' COMMENT '播报内容',
  `push_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '播报金额',
  `push_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '推送状态 0=待推送 1=推送中 2=成功 3=失败',
  `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '失败原因',
  `push_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '推送时间',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_mer_id` (`mer_id`),
  KEY `idx_device_id` (`device_id`),
  KEY `idx_order_sn` (`order_sn`),
  KEY `idx_push_status` (`push_status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='语音播报推送日志表';
