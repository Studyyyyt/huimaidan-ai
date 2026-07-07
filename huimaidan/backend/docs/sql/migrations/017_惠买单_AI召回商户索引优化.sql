-- 惠买单 AI 召回商户索引优化
-- 目的：优化 AI 推荐召回中常用的城市、状态、商户 ID 过滤，并保持迁移可重复执行。

SET NAMES utf8mb4;

DELIMITER $$

DROP PROCEDURE IF EXISTS AddHuimaidanIndexIfNotExists$$

CREATE PROCEDURE AddHuimaidanIndexIfNotExists(
  IN p_table VARCHAR(64),
  IN p_index VARCHAR(64),
  IN p_ddl VARCHAR(1024)
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = p_table
      AND index_name = p_index
  ) THEN
    SET @sql = p_ddl;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

DELIMITER ;

CALL AddHuimaidanIndexIfNotExists(
  'eb_merchant',
  'idx_hmd_ai_recall_city_status',
  'ALTER TABLE `eb_merchant` ADD INDEX `idx_hmd_ai_recall_city_status` (`city_id`, `is_del`, `status`, `mer_state`, `mer_id`)'
);

CALL AddHuimaidanIndexIfNotExists(
  'eb_merchant',
  'idx_hmd_ai_recall_status',
  'ALTER TABLE `eb_merchant` ADD INDEX `idx_hmd_ai_recall_status` (`is_del`, `status`, `mer_state`, `mer_id`)'
);

DROP PROCEDURE IF EXISTS AddHuimaidanIndexIfNotExists;

UPDATE `eb_huimaidan_ai_config`
SET `config_value` = '12',
    `config_desc` = 'LLM动态排序超时秒数；超过后快速回退规则排序，避免用户长时间等待'
WHERE `config_key` = 'llm_rerank_timeout'
  AND (
    `config_value` = ''
    OR CAST(`config_value` AS UNSIGNED) = 0
    OR CAST(`config_value` AS UNSIGNED) > 20
  );

UPDATE `eb_huimaidan_ai_config`
SET `config_value` = '12',
    `config_desc` = 'DeepSeek API 请求超时秒数；建议 10-15 秒，超时后走兜底推荐'
WHERE `config_key` = 'deepseek_timeout'
  AND (
    `config_value` = ''
    OR CAST(`config_value` AS UNSIGNED) > 15
  );

UPDATE `eb_huimaidan_ai_config`
SET `config_value` = '0',
    `config_desc` = 'LLM失败重试次数；线上推荐默认不重试，避免用户长时间等待'
WHERE `config_key` = 'llm_retry_times';
