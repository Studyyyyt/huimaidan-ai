-- 惠买单店铺浏览历史表
-- 更新时间：2026-06-18
-- 前置条件：已部署 CRMEB MER v3.4，数据库表前缀为 eb_。

CREATE TABLE IF NOT EXISTS `eb_user_merchant_history` (
  `user_merchant_history_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '店铺浏览历史ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `mer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商户ID',
  `visit_count` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '浏览次数',
  `last_visit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后浏览时间',
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`user_merchant_history_id`) USING BTREE,
  UNIQUE KEY `uniq_uid_mer_id` (`uid`, `mer_id`) USING BTREE,
  KEY `idx_uid_last_visit` (`uid`, `last_visit_time`) USING BTREE,
  KEY `idx_mer_id` (`mer_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户店铺浏览历史';

-- 回滚 SQL：
-- DROP TABLE IF EXISTS `eb_user_merchant_history`;
