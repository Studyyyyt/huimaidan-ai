-- 清理因为错误字符集导入产生的惠买单 AI 标签乱码数据。
-- 正常中文标签不会命中这些拉丁乱码字符，可重复执行。

SET NAMES utf8mb4;

DELETE FROM `eb_huimaidan_merchant_tag`
WHERE `tag_value` REGEXP '[ÃÂæäåçèéêëìíîïòóôõöùúûü¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿]';

DELETE FROM `eb_huimaidan_ai_tag`
WHERE `tag_value` REGEXP '[ÃÂæäåçèéêëìíîïòóôõöùúûü¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿]'
   OR `tag_label` REGEXP '[ÃÂæäåçèéêëìíîïòóôõöùúûü¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿]'
   OR CAST(`synonyms` AS CHAR) REGEXP '[ÃÂæäåçèéêëìíîïòóôõöùúûü¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿]';
