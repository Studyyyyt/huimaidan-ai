-- 惠买单 AI 动态排序与配置向导优化迁移
-- 新增 LLM 动态排序配置项、扩展推荐日志字段

-- MySQL 8.0 不支持 ALTER TABLE ADD COLUMN IF NOT EXISTS，使用存储过程兼容
DELIMITER $$

DROP PROCEDURE IF EXISTS AddAiLogColumnIfNotExists$$

CREATE PROCEDURE AddAiLogColumnIfNotExists(
  IN p_table VARCHAR(64),
  IN p_column VARCHAR(64),
  IN p_type VARCHAR(255),
  IN p_comment VARCHAR(255)
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = p_table
      AND column_name = p_column
  ) THEN
    SET @sql = CONCAT(
      'ALTER TABLE ', p_table,
      ' ADD COLUMN ', p_column, ' ', p_type,
      ' COMMENT ', QUOTE(p_comment)
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

DELIMITER ;

CALL AddAiLogColumnIfNotExists(
  'eb_huimaidan_ai_recommend_log',
  'candidate_mer_ids_before',
  'json DEFAULT NULL',
  'LLM排序前候选商户ID'
);

CALL AddAiLogColumnIfNotExists(
  'eb_huimaidan_ai_recommend_log',
  'candidate_mer_ids_after',
  'json DEFAULT NULL',
  'LLM排序后候选商户ID'
);

DROP PROCEDURE IF EXISTS AddAiLogColumnIfNotExists;

-- 修正旧配置中 max_tokens 过小导致 AI 回复被截断的问题（仅当当前值小于 512 时）
UPDATE `eb_huimaidan_ai_config`
SET `config_value` = '512',
    `config_desc` = '百炼兼容模式最大输出token数，低于512可能导致推荐理由被截断'
WHERE `config_key` = 'bailian_max_tokens'
  AND CAST(`config_value` AS UNSIGNED) < 512;

INSERT INTO `eb_huimaidan_ai_config`
(`config_key`,`config_value`,`config_desc`,`sort`)
VALUES
('llm_rerank_enabled','1','是否启用 LLM 动态排序：1启用 0禁用',180),
('llm_rerank_candidate_limit','12','LLM排序候选池上限，建议 8-15，过大会明显变慢',181),
('llm_rerank_result_limit','3','LLM动态排序最终返回商户数量',182),
('llm_rerank_fallback_enabled','1','LLM排序失败是否回退规则排序：1是 0否',183),
('llm_rerank_timeout','0','LLM排序调用超时秒数，0表示沿用模型默认超时',184),
('llm_rerank_max_tokens','1024','LLM动态排序最大输出token数，JSON排序结果通常需要更大空间',185),
('merchant_ai_health_min_score','70','商户AI完整度提醒阈值，低于此分数提示待完善',186),
('prompt_rerank_system','你是"惠买单"本地生活推荐系统的排序专家。任务：根据用户原话、意图标签和候选商户列表，对候选商户进行动态排序，并生成每家商户的推荐理由。重要约束：1.只能对提供的候选商户排序，不允许编造、新增或删除候选商户。2.充分理解复杂偏好：距离、价格、评分、折扣、口味、设施、场景、人群、营业状态等。3.优先按用户核心偏好排序。4.不要改变商户 factual 信息。输出格式：严格返回JSON {"summary":"...","items":[{"mer_id":1,"rank":1,"reason":"..."}]}','LLM动态排序System Prompt',190),
('prompt_rerank','用户原话：{message}\n历史对话：{history}\n意图标签：{intent}\n候选商户：{candidates}\n\n请根据用户原话和意图标签，对候选商户进行排序并生成推荐理由。严格返回JSON格式。','LLM动态排序User Prompt',191)
ON DUPLICATE KEY UPDATE
`config_value` = VALUES(`config_value`),
`config_desc` = VALUES(`config_desc`),
`sort` = VALUES(`sort`);
