-- 惠买单店铺二维码菜单权限 SQL
-- 更新时间：2026-06-17
--
-- 前置条件：
-- 1. 已执行 `docs/sql/惠买单_后端菜单权限.sql`，存在平台后台惠买单根菜单 20000。
-- 2. 已执行 `docs/sql/惠买单_商户端菜单权限.sql`，存在商户端惠买单根菜单 20018。
-- 3. 已存在商户类型表 `eb_merchant_type`。
--
-- 说明：
-- 1. 本脚本只补充店铺二维码菜单与权限节点。
-- 2. 使用 20100 段 menu_id，避免与既有惠买单菜单 ID 冲突。
-- 3. 商户端 route 字段不要包含 /merchant 或 /mer 前缀，CRMEB 会按端自动拼接。

INSERT IGNORE INTO `eb_system_menu`
(`menu_id`,`pid`,`path`,`icon`,`menu_name`,`route`,`params`,`sort`,`is_show`,`is_mer`,`is_menu`,`create_time`,`update_time`,`is_agent`)
VALUES
(20100,20000,'/20000/','','店铺二维码管理','/huimaidan/store-qrcode','[]',5,1,0,1,NOW(),NOW(),0),
(20101,20100,'/20000/20100/','','列表','adminHuimaidanStoreQrcodeLst','',1,1,0,2,NOW(),NOW(),0),
(20102,20100,'/20000/20100/','','详情','adminHuimaidanStoreQrcodeDetail','',2,1,0,2,NOW(),NOW(),0),
(20103,20100,'/20000/20100/','','强制刷新','adminHuimaidanStoreQrcodeRefresh','',3,1,0,2,NOW(),NOW(),0),
(20104,20100,'/20000/20100/','','下载','adminHuimaidanStoreQrcodeDownload','',4,1,0,2,NOW(),NOW(),0),
(20110,20018,'/20018/','','店铺二维码','/huimaidan/store-qrcode','[]',6,1,1,1,NOW(),NOW(),0),
(20111,20110,'/20018/20110/','','详情','merchantHuimaidanStoreQrcodeDetail','',1,1,1,0,NOW(),NOW(),0),
(20112,20110,'/20018/20110/','','刷新','merchantHuimaidanStoreQrcodeRefresh','',2,1,1,0,NOW(),NOW(),0),
(20113,20110,'/20018/20110/','','下载','merchantHuimaidanStoreQrcodeDownload','',3,1,1,0,NOW(),NOW(),0);

INSERT INTO `eb_relevance` (`left_id`,`right_id`,`type`)
SELECT mt.mer_type_id, t.right_id, 'mer_auth'
FROM `eb_merchant_type` mt
JOIN (
    SELECT 20110 AS right_id
    UNION ALL SELECT 20111
    UNION ALL SELECT 20112
    UNION ALL SELECT 20113
) AS t
WHERE NOT EXISTS (
    SELECT 1
    FROM `eb_relevance` r
    WHERE r.left_id = mt.mer_type_id
      AND r.right_id = t.right_id
      AND r.type = 'mer_auth'
);

-- 回滚说明：
-- DELETE FROM `eb_relevance` WHERE `type` = 'mer_auth' AND `right_id` IN (20110,20111,20112,20113);
-- DELETE FROM `eb_system_menu` WHERE `menu_id` IN (20100,20101,20102,20103,20104,20110,20111,20112,20113);
