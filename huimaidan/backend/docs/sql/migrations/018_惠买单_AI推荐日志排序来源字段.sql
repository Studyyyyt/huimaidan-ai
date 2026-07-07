-- 惠买单 AI 推荐日志排序来源字段
-- 目的：让运营和交付验收能直观看到本次推荐由 LLM 排序还是规则兜底产生。

SET NAMES utf8mb4;

DELIMITER $$

DROP PROCEDURE IF EXISTS AddHuimaidanAiLogColumnIfNotExists$$

CREATE PROCEDURE AddHuimaidanAiLogColumnIfNotExists(
  IN p_column VARCHAR(64),
  IN p_type VARCHAR(255),
  IN p_comment VARCHAR(255)
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'eb_huimaidan_ai_recommend_log'
      AND column_name = p_column
  ) THEN
    SET @sql = CONCAT(
      'ALTER TABLE `eb_huimaidan_ai_recommend_log`',
      ' ADD COLUMN `', p_column, '` ', p_type,
      ' COMMENT ', QUOTE(p_comment)
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$

DELIMITER ;

CALL AddHuimaidanAiLogColumnIfNotExists(
  'rerank_source',
  'varchar(32) NOT NULL DEFAULT ''''',
  '排序来源：llm/rule_fallback/none'
);

CALL AddHuimaidanAiLogColumnIfNotExists(
  'fallback_reason',
  'varchar(255) NOT NULL DEFAULT ''''',
  'LLM排序降级或回退原因'
);

DROP PROCEDURE IF EXISTS AddHuimaidanAiLogColumnIfNotExists;
