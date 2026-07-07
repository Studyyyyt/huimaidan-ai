-- 惠买单一期后端数据库变更
-- 执行前请先备份数据库；如果字段已存在，请跳过对应 ALTER。

CREATE TABLE IF NOT EXISTS `eb_capital_pool` (
  `pool_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '垫资池ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '当前余额',
  `total_recharge` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值/调增',
  `total_consume` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '累计扣减/调减',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0禁用 1启用',
  `alarm_balance` decimal(12,2) NOT NULL DEFAULT '100.00' COMMENT '余额预警值',
  `alarm_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启预警',
  `last_alarm_time` datetime DEFAULT NULL COMMENT '最后预警时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`pool_id`) USING BTREE,
  UNIQUE KEY `uniq_mer_id` (`mer_id`) USING BTREE,
  KEY `idx_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单商户垫资池';

CREATE TABLE IF NOT EXISTS `eb_pool_transaction` (
  `transaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '垫资池流水ID',
  `pool_id` int(10) unsigned NOT NULL COMMENT '垫资池ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `type` tinyint(1) NOT NULL COMMENT '类型 1充值 2订单扣减 3调整增加 4调整减少',
  `amount` decimal(12,2) NOT NULL COMMENT '变动金额',
  `balance_before` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '变动前余额',
  `balance_after` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '变动后余额',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联订单ID',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '后台管理员ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`transaction_id`) USING BTREE,
  KEY `idx_pool_id` (`pool_id`) USING BTREE,
  KEY `idx_mer_id` (`mer_id`) USING BTREE,
  KEY `idx_order_type` (`order_id`,`type`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单垫资池流水';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_discount_rule` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `pool_id` int(10) unsigned DEFAULT NULL COMMENT '关联垫资池ID',
  `rule_type` tinyint(1) NOT NULL COMMENT '规则类型 1折扣 2立减 3积分抵扣',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '规则名称',
  `platform_discount` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '平台折扣，如0.60',
  `merchant_cost` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '商家底价折扣，如0.50',
  `coupon_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '立减金额',
  `point_ratio` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '积分抵扣比例',
  `min_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '最低消费金额',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0关闭 1开启',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`rule_id`) USING BTREE,
  KEY `idx_mer_status` (`mer_id`,`status`,`is_del`) USING BTREE,
  KEY `idx_pool_id` (`pool_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单优惠规则';

ALTER TABLE `eb_store_order` ADD COLUMN `pool_id` int(10) unsigned DEFAULT NULL COMMENT '惠买单关联垫资池ID';
ALTER TABLE `eb_store_order` ADD COLUMN `merchant_cost_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '惠买单商家底价金额';
ALTER TABLE `eb_store_order` ADD COLUMN `platform_profit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '惠买单平台差价收入';
ALTER TABLE `eb_store_order` ADD COLUMN `discount_snapshot` text COMMENT '惠买单优惠快照(JSON)';
ALTER TABLE `eb_store_order` ADD COLUMN `pool_transaction_id` int(10) unsigned DEFAULT NULL COMMENT '惠买单垫资池流水ID';
ALTER TABLE `eb_store_order` ADD COLUMN `order_scene` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单场景 0普通订单 1惠买单到店买单';
ALTER TABLE `eb_store_order` ADD KEY `idx_huimaidan_scene_paid` (`order_scene`,`paid`,`mer_id`,`pay_time`);
