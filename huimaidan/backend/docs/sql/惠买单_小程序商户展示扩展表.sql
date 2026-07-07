-- 惠买单小程序商户展示扩展表
-- 执行前请先备份数据库。
-- 本表只存惠买单小程序展示扩展字段，商户基础资料仍以 eb_merchant 为准。

CREATE TABLE IF NOT EXISTS `eb_huimaidan_merchant_profile` (
  `profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '惠买单商户展示配置ID',
  `mer_id` int(10) unsigned NOT NULL COMMENT '商户ID，对应eb_merchant.mer_id',
  `branch_name` varchar(64) NOT NULL DEFAULT '' COMMENT '分店名，如摩尔城店',
  `configured_sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '手动配置销量，展示销量=配置销量+真实销量',
  `per_capita` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '人均消费',
  `business_hours` text COMMENT '营业时间JSON数组',
  `facilities` text COMMENT '设施标签JSON，如大桌、宝宝椅、电话预订、无烟区',
  `promo_image` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序促销图',
  `slogan` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序商户标语',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`profile_id`) USING BTREE,
  UNIQUE KEY `uniq_mer_id` (`mer_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='惠买单小程序商户展示扩展配置';
