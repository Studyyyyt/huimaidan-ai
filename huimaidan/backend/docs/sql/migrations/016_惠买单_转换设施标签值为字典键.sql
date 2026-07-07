-- +----------------------------------------------------------------------
-- | 惠买单 AI 推荐功能修复 - 转换已有设施标签值为字典键
-- +----------------------------------------------------------------------
-- | 说明：
-- | 设施标签字典化后，eb_huimaidan_ai_tag.tag_value 使用英文键（如 has_large_table）。
-- | 原先 MerchantTagInitializerRepository 生成的 merchant tag 使用中文标签值（如 大桌），
-- | 会导致 NLU 输出与商户标签无法匹配。本迁移把历史 facility 类型标签值统一转换为英文键。
-- +----------------------------------------------------------------------

SET NAMES utf8mb4;

UPDATE `eb_huimaidan_merchant_tag`
SET `tag_value` = CASE `tag_value`
  WHEN '大桌' THEN 'has_large_table'
  WHEN '宝宝椅' THEN 'has_baby_chair'
  WHEN '包间' THEN 'has_private_room'
  WHEN '电话预订' THEN 'can_phone_reserve'
  WHEN '无烟' THEN 'is_non_smoking'
  WHEN '无烟餐厅' THEN 'is_non_smoking'
  ELSE `tag_value`
END
WHERE `tag_type` = 'facility'
  AND `tag_value` IN ('大桌','宝宝椅','包间','电话预订','无烟','无烟餐厅');
