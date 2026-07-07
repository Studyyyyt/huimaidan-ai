CREATE TABLE IF NOT EXISTS `eb_huimaidan_ai_tag` (
  `tag_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'AI标签ID',
  `tag_type` varchar(32) NOT NULL DEFAULT '' COMMENT '标签类型:category|scene|taste|facility|price|feature|meal|promotion',
  `tag_value` varchar(64) NOT NULL DEFAULT '' COMMENT '标签值',
  `tag_label` varchar(64) NOT NULL DEFAULT '' COMMENT '展示名称',
  `synonyms` json DEFAULT NULL COMMENT '同义词列表',
  `tag_weight` int NOT NULL DEFAULT 10 COMMENT '默认权重1-100',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1启用0禁用',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `uk_type_value` (`tag_type`,`tag_value`),
  KEY `idx_type_status_sort` (`tag_type`,`status`,`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单AI标签字典';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_merchant_tag` (
  `tag_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商户AI标签ID',
  `mer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商户ID',
  `tag_type` varchar(32) NOT NULL DEFAULT '' COMMENT '标签类型',
  `tag_value` varchar(64) NOT NULL DEFAULT '' COMMENT '标签值',
  `tag_weight` int NOT NULL DEFAULT 10 COMMENT '商户标签权重1-100',
  `is_auto` tinyint NOT NULL DEFAULT 1 COMMENT '1自动生成0人工维护',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `uk_mer_tag` (`mer_id`,`tag_type`,`tag_value`),
  KEY `idx_tag_type_value` (`tag_type`,`tag_value`),
  KEY `idx_mer_id` (`mer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单商户AI标签';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_ai_banner_config` (
  `config_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Banner配置ID',
  `meal_type` varchar(32) NOT NULL DEFAULT '' COMMENT 'breakfast|brunch|lunch|tea|dinner|supper|late_night',
  `title_template` varchar(128) NOT NULL DEFAULT '' COMMENT '标题模板',
  `subtitle_template` varchar(256) NOT NULL DEFAULT '' COMMENT '副标题模板',
  `bg_color` varchar(16) NOT NULL DEFAULT '#FFF3E0' COMMENT '背景色',
  `text_color` varchar(16) NOT NULL DEFAULT '#E65100' COMMENT '文字色',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `is_enabled` tinyint NOT NULL DEFAULT 1 COMMENT '1启用0禁用',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uk_meal_type` (`meal_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单AI Banner餐段配置';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_ai_config` (
  `config_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'AI配置ID',
  `config_key` varchar(64) NOT NULL DEFAULT '' COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单AI推荐参数配置';

ALTER TABLE `eb_huimaidan_ai_config`
  MODIFY `config_value` text COMMENT '配置值';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_ai_recommend_log` (
  `log_id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '推荐日志ID',
  `uid` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `session_id` varchar(64) NOT NULL DEFAULT '' COMMENT '会话ID',
  `query_text` varchar(512) NOT NULL DEFAULT '' COMMENT '用户输入',
  `intent_tags` json DEFAULT NULL COMMENT '意图标签',
  `recall_count` int NOT NULL DEFAULT 0 COMMENT '召回数量',
  `result_mer_ids` json DEFAULT NULL COMMENT '推荐商户ID',
  `degraded` tinyint NOT NULL DEFAULT 0 COMMENT '是否降级',
  `error_message` varchar(255) NOT NULL DEFAULT '' COMMENT '错误信息',
  `user_feedback` tinyint DEFAULT NULL COMMENT '1点赞0无反馈-1点踩',
  `click_mer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '点击商户ID',
  `order_mer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '下单商户ID',
  `response_time_ms` int unsigned NOT NULL DEFAULT 0 COMMENT '响应耗时',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_uid_time` (`uid`,`create_time`),
  KEY `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单AI推荐日志';

CREATE TABLE IF NOT EXISTS `eb_huimaidan_user_preference` (
  `pref_id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户偏好ID',
  `uid` int unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `pref_type` varchar(32) NOT NULL DEFAULT '' COMMENT 'category|scene|price|feature|taste',
  `pref_value` varchar(64) NOT NULL DEFAULT '' COMMENT '偏好值',
  `pref_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '偏好得分',
  `source_count` int NOT NULL DEFAULT 0 COMMENT '样本数',
  `last_update` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pref_id`),
  UNIQUE KEY `uk_uid_type_value` (`uid`,`pref_type`,`pref_value`),
  KEY `idx_uid_score` (`uid`,`pref_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='惠买单AI用户偏好预留';

INSERT INTO `eb_huimaidan_ai_banner_config`
(`meal_type`,`title_template`,`subtitle_template`,`bg_color`,`text_color`,`sort`,`is_enabled`)
VALUES
('breakfast','早餐时间到！','为您推荐附近营业中的优惠早餐','#FFF8E1','#F57F17',10,1),
('brunch','早午餐吃点什么？','轻松找一家离你近、评价好的店','#F3E5F5','#6A1B9A',20,1),
('lunch','午餐时间到！','为您推荐附近高分又有优惠的好店','#FFF3E0','#E65100',30,1),
('tea','下午茶时光','甜品茶饮和轻食优惠都在这里','#E8F5E9','#2E7D32',40,1),
('dinner','晚餐推荐','适合聚餐、约会的优惠商家已备好','#FFEBEE','#C62828',50,1),
('supper','夜宵时间','附近还在营业的夜宵好店推荐','#E3F2FD','#1565C0',60,1),
('late_night','深夜食堂','夜深了，也能找到热乎的优惠好味','#ECEFF1','#37474F',70,1)
ON DUPLICATE KEY UPDATE
`title_template` = VALUES(`title_template`),
`subtitle_template` = VALUES(`subtitle_template`),
`bg_color` = VALUES(`bg_color`),
`text_color` = VALUES(`text_color`),
`sort` = VALUES(`sort`),
`is_enabled` = VALUES(`is_enabled`);

INSERT INTO `eb_huimaidan_ai_config`
(`config_key`,`config_value`,`config_desc`,`sort`)
VALUES
('score_weight_tag','0.35','标签匹配权重',10),
('score_weight_distance','0.25','距离权重',20),
('score_weight_heat','0.25','热度权重',30),
('score_weight_promo','0.15','优惠权重',40),
('recall_radius_km','5','默认召回半径公里',50),
('recall_max_candidates','50','候选商户上限',60),
('result_limit','3','对话推荐返回商户数量',70),
('daily_chat_limit','50','单用户每日对话上限',80),
('input_max_length','200','用户单次输入最大字数',90),
('sensitive_words','','敏感词配置，多个词用逗号或换行分隔',100),
('llm_driver','bailian','LLM驱动:bailian|deepseek|claude',110),
('llm_retry_times','0','LLM失败重试次数',120),
('llm_retry_sleep_ms','200','LLM失败重试间隔毫秒',130),
('llm_fail_threshold','3','LLM连续失败熔断阈值',140),
('llm_recovery_seconds','900','LLM熔断恢复秒数',150),
('prompt_nlu','你是惠买单本地生活推荐系统的意图理解模块。请从用户输入中提取可计算标签，严格输出 JSON，不要输出解释。允许字段: category数组, scene数组, taste数组, facility数组, price或price_range字符串, meal或time数组或字符串, people字符串, distance字符串, action字符串。价格区间只能是 0-30,30-60,60-100,100-150,150+，也可返回 0-60 或 0-100。当前规则解析结果:{fallback}历史对话:{history}用户输入:{message}','NLU意图解析Prompt模板',160),
('prompt_reasoning','你是惠买单小程序的本地生活推荐助手。后端已经完成商户排序，请不要改变商户顺序，不要编造商户、优惠或距离。请基于候选商户和推荐理由，生成一句自然、简短、适合展示给用户的回复文案。严格输出 JSON，格式为 {"text":"..."}，不要输出解释。用户意图:{intent}候选商户:{merchants}兜底文案:{fallback_text}','推荐回复Prompt模板',170)
ON DUPLICATE KEY UPDATE
`config_value` = VALUES(`config_value`),
`config_desc` = VALUES(`config_desc`),
`sort` = VALUES(`sort`);
