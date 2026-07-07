-- +----------------------------------------------------------------------
-- | 惠买单 AI 推荐功能修复 - 设施标签字典化与 AI 配置默认值
-- +----------------------------------------------------------------------
-- | 说明：
-- | 1. 把原先硬编码在 MerchantProfileRepository::FACILITY_LABELS 中的 5 个设施标签
-- |    迁移到 eb_huimaidan_ai_tag 字典表，tag_type='facility'。
-- | 2. 写入 AI 配置默认值，避免模式 fallback 和 DeepSeek thinking 字段导致调用异常。
-- +----------------------------------------------------------------------

-- 设施标签字典化
INSERT INTO `eb_huimaidan_ai_tag` (`tag_type`,`tag_value`,`tag_label`,`synonyms`,`tag_weight`,`sort`,`status`)
VALUES
  ('facility','has_large_table','大桌','["大桌子","大桌台"]',10,10,1),
  ('facility','has_baby_chair','宝宝椅','["儿童椅","婴儿椅"]',10,20,1),
  ('facility','has_private_room','包间','["包厢","包房","私密包间"]',10,30,1),
  ('facility','can_phone_reserve','电话预订','["电话预约","可预订"]',10,40,1),
  ('facility','is_non_smoking','无烟餐厅','["无烟区","禁止吸烟"]',10,50,1)
ON DUPLICATE KEY UPDATE
  `tag_label` = VALUES(`tag_label`),
  `synonyms` = VALUES(`synonyms`),
  `tag_weight` = VALUES(`tag_weight`),
  `sort` = VALUES(`sort`),
  `status` = VALUES(`status`);

-- AI 配置默认值
INSERT INTO `eb_huimaidan_ai_config` (`config_key`,`config_value`,`config_desc`,`sort`)
VALUES
  ('bailian_mode','app','百炼调用模式：app 为应用模式，compatible 为 OpenAI 兼容模式',111),
  ('deepseek_thinking_type','disabled','DeepSeek thinking 类型：enabled 开启，disabled 关闭（官方 API 默认不支持 thinking 时请勿开启）',250),
  ('tencent_map_js_key','','腾讯地图 Web JS Key，用于 AI 后台商户资料地图选点',260)
ON DUPLICATE KEY UPDATE
  `config_value` = VALUES(`config_value`),
  `config_desc` = VALUES(`config_desc`),
  `sort` = VALUES(`sort`);

-- 新手引导默认配置（首次由后端自动创建，此处仅作为参考 SQL）
-- INSERT INTO `eb_huimaidan_ai_config` (`config_key`,`config_value`,`config_desc`,`sort`)
-- VALUES
--   ('onboarding_config','{"enabled":1,"title":"AI 小惠能帮你什么？","tips":["附近有什么好吃的","按预算筛选餐厅","适合约会/聚餐/带娃的场景","导航到店"],"samples":["附近好吃的火锅","人均 50 左右的川菜","适合带娃的餐厅"]}','AI 对话页新手引导配置',300);
