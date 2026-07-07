-- ============================================================
-- 惠买单：店铺简介改为富文本（支持图片）
-- 执行日期：2026-06-23
-- ============================================================
-- 注意：店铺标签（大桌、宝宝椅、包间、电话预订、无烟餐厅）
--       存储在 eb_huimaidan_merchant_profile.facilities JSON 字段中，
--       无需新增列，PHP 代码已通过 FACILITY_LABELS 常量管理。

-- 店铺简介字段改为 TEXT，支持富文本 HTML
ALTER TABLE `eb_merchant`
  MODIFY COLUMN `mer_info` text COMMENT '店铺简介（富文本HTML）';
