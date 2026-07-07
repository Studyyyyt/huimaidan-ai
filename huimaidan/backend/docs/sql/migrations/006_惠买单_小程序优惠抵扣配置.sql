-- 惠买单小程序第 4 批：优惠券/积分真实抵扣配置
-- 执行前请先备份数据库。
-- 本脚本只新增后台配置项元数据，不写入 eb_system_config_value 默认值。
-- 后台未保存 huimaidan_discount_stack_enabled 时，下单接口会直接报错：请先配置惠买单优惠叠加策略。

INSERT INTO `eb_system_config_classify`
    (`pid`, `classify_name`, `classify_key`, `info`, `sort`, `icon`, `status`)
SELECT
    0, '惠买单配置', 'huimaidan_config', '惠买单配置', 0, '', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `eb_system_config_classify` WHERE `classify_key` = 'huimaidan_config'
);

SET @huimaidan_config_classify_id := (
    SELECT `config_classify_id`
    FROM `eb_system_config_classify`
    WHERE `classify_key` = 'huimaidan_config'
    LIMIT 1
);

INSERT INTO `eb_system_config`
    (`config_classify_id`, `config_name`, `config_key`, `config_type`, `config_rule`, `config_props`, `required`, `info`, `sort`, `user_type`, `status`, `linked_status`, `linked_id`, `linked_value`)
SELECT
    @huimaidan_config_classify_id,
    '惠买单优惠叠加策略',
    'huimaidan_discount_stack_enabled',
    'radio',
    '0:不叠加\n1:叠加',
    '',
    1,
    '控制惠买单下单时商户/会员折扣、优惠券、积分是否允许叠加；优惠券和积分固定由平台承担。',
    0,
    0,
    1,
    0,
    0,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM `eb_system_config` WHERE `config_key` = 'huimaidan_discount_stack_enabled'
);

-- 执行后操作：
-- 1. 在平台后台保存“惠买单优惠叠加策略”，值为 0 或 1。
-- 2. 如使用积分抵扣，还需确认现有积分配置 integral_status、integral_money 已配置。
-- 3. 清理系统配置缓存；长驻进程环境需重启 Swoole/队列进程。
--
-- 回滚说明：
-- DELETE FROM `eb_system_config_value` WHERE `config_key` = 'huimaidan_discount_stack_enabled' AND `mer_id` = 0;
-- DELETE FROM `eb_system_config` WHERE `config_key` = 'huimaidan_discount_stack_enabled';
-- DELETE FROM `eb_system_config_classify` WHERE `classify_key` = 'huimaidan_config'
--   AND NOT EXISTS (SELECT 1 FROM `eb_system_config` WHERE `config_classify_id` = @huimaidan_config_classify_id);
