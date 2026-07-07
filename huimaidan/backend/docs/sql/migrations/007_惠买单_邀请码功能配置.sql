-- 惠买单邀请码功能配置
-- 在系统配置中添加邀请奖励上限配置项

INSERT INTO `eb_system_config`
(`config_classify_id`, `config_name`, `config_key`, `config_type`, `config_rule`, `config_props`, `required`, `info`, `sort`, `user_type`, `status`, `linked_status`, `linked_id`, `linked_value`)
SELECT
    0,
    '邀请奖励上限',
    'integral_user_give_limit',
    'number',
    '',
    '',
    0,
    '同一邀请人通过邀请好友最多获得多少次积分奖励，0表示不限制',
    0,
    0,
    1,
    0,
    0,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM `eb_system_config` WHERE `config_key` = 'integral_user_give_limit'
);

INSERT INTO `eb_system_config_value` (`config_key`, `value`, `mer_id`)
SELECT 'integral_user_give_limit', '0', 0
WHERE NOT EXISTS (
    SELECT 1 FROM `eb_system_config_value` WHERE `config_key` = 'integral_user_give_limit' AND `mer_id` = 0
);
