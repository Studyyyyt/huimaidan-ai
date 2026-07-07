-- 惠买单三期提现模式后端数据库变更
-- 执行前请先备份数据库；如果字段或索引已存在，请跳过对应 ALTER。

ALTER TABLE `eb_merchant`
    ADD COLUMN `huimaidan_settlement_mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '惠买单合作模式 1垫资池 2提现模式' AFTER `mer_money`,
    ADD COLUMN `huimaidan_withdraw_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '惠买单提现手续费率(%)' AFTER `huimaidan_settlement_mode`;

ALTER TABLE `eb_store_order`
    ADD COLUMN `settlement_mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '惠买单结算模式 1垫资池 2提现模式' AFTER `order_scene`,
    ADD COLUMN `huimaidan_income_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '惠买单入账状态 0未入账 1已入账' AFTER `settlement_mode`,
    ADD KEY `idx_huimaidan_settlement_income` (`order_scene`,`settlement_mode`,`huimaidan_income_status`,`paid`);

ALTER TABLE `eb_financial`
    ADD COLUMN `business_type` varchar(32) NOT NULL DEFAULT '' COMMENT '业务来源，如huimaidan' AFTER `type`,
    ADD COLUMN `fee_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '手续费率(%)' AFTER `business_type`,
    ADD COLUMN `fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '手续费金额' AFTER `fee_rate`,
    ADD COLUMN `real_transfer_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '实际打款金额' AFTER `fee_amount`,
    ADD COLUMN `account_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收款码类型 2微信 3支付宝' AFTER `real_transfer_amount`,
    ADD COLUMN `trade_channel` varchar(32) NOT NULL DEFAULT '' COMMENT '交易渠道，如huimaidan_withdraw' AFTER `account_type`,
    ADD COLUMN `audit_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核备注' AFTER `trade_channel`,
    ADD KEY `idx_huimaidan_withdraw` (`business_type`,`mer_id`,`status`,`financial_status`,`is_del`),
    ADD KEY `idx_huimaidan_account_type` (`business_type`,`account_type`);

ALTER TABLE `eb_huimaidan_merchant_discount`
    MODIFY COLUMN `pool_id` int(10) unsigned DEFAULT NULL COMMENT '关联垫资池ID，提现模式允许为空';

-- 商户最低提现金额（按商户配置，替代原硬编码的500元）
ALTER TABLE `eb_merchant`
    ADD COLUMN `huimaidan_min_extract_money` decimal(10,2) NOT NULL DEFAULT '500.00' COMMENT '惠买单最低提现金额' AFTER `huimaidan_withdraw_rate`;
