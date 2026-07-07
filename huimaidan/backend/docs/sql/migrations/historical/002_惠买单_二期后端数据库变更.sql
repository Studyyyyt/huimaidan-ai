-- 惠买单二期后端数据库变更
-- 执行前请先备份数据库。
-- 如果 eb_merchant.city_id 已存在，请跳过对应 ALTER。

ALTER TABLE `eb_merchant`
    ADD COLUMN `city_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '惠买单所属城市ID，对应eb_city_area.id' AFTER `category_id`,
    ADD KEY `idx_huimaidan_city` (`city_id`);

CREATE TABLE IF NOT EXISTS `eb_pool_alarm_record` (
  `alarm_record_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '预警记录ID',
  `pool_id` int(10) unsigned NOT NULL COMMENT '垫资池ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '触发预警时余额',
  `alarm_balance` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '触发时预警阈值',
  `source` varchar(32) NOT NULL DEFAULT '' COMMENT '触发来源 deduct订单扣减',
  `notice_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '通知状态 0待通知 1成功 2失败',
  `notice_message` varchar(255) NOT NULL DEFAULT '' COMMENT '通知结果或失败原因',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`alarm_record_id`) USING BTREE,
  KEY `idx_pool_id` (`pool_id`) USING BTREE,
  KEY `idx_mer_id` (`mer_id`) USING BTREE,
  KEY `idx_notice_status` (`notice_status`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单垫资池预警历史';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_merchant_discount` (
  `discount_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '惠买单商家折扣配置ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `pool_id` int(10) unsigned NOT NULL COMMENT '关联垫资池ID',
  `merchant_discount` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '商家结算折扣，如0.60',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0停用 1启用',
  `start_time` datetime DEFAULT NULL COMMENT '生效开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '生效结束时间',
  `sort` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '后台备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`discount_id`) USING BTREE,
  KEY `idx_mer_status` (`mer_id`,`status`) USING BTREE,
  KEY `idx_pool_id` (`pool_id`) USING BTREE,
  KEY `idx_active_time` (`start_time`,`end_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单商家折扣配置';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_member_discount` (
  `member_discount_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '惠买单会员等级折扣ID',
  `discount_id` int(10) unsigned NOT NULL COMMENT '商家折扣配置ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID',
  `member_level` int(10) NOT NULL COMMENT '用户等级值，对应user.member_level',
  `member_discount` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT '会员消费折扣，如0.80',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 0停用 1启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`member_discount_id`) USING BTREE,
  UNIQUE KEY `uniq_discount_level` (`discount_id`,`member_level`) USING BTREE,
  KEY `idx_mer_level` (`mer_id`,`member_level`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单商家会员等级消费折扣';
