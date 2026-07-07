INSERT INTO `eb_system_config`
(`config_name`, `field_name`, `input_type`, `config_tab_id`, `parameter`, `required`, `info`, `sort`, `status`, `config_classify_id`, `create_time`, `update_time`)
SELECT '邀请奖励上限', 'integral_user_give_limit', 'number', 57, '', 0, '同一邀请人通过邀请好友最多获得多少次积分奖励，0表示不限制', 0, 1, 0, NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `eb_system_config` WHERE `field_name` = 'integral_user_give_limit'
);

INSERT INTO `eb_system_config_value` (`config_key`, `config_value`, `mer_id`)
SELECT 'integral_user_give_limit', '0', 0
WHERE NOT EXISTS (
  SELECT 1 FROM `eb_system_config_value` WHERE `config_key` = 'integral_user_give_limit' AND `mer_id` = 0
);
