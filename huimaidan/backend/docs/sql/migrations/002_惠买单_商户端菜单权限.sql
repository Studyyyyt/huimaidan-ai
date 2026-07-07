-- 惠买单商户端菜单权限 SQL
-- 说明：
-- 1. 仅补充商户后台可见菜单与对应隐藏权限。
-- 2. 当前安装库里已存在多个商户类型，以下关联会自动挂到所有现有商户类型上。
-- 3. `eb_system_menu` 用固定 `menu_id`，可重复执行；`eb_relevance` 用 NOT EXISTS 防止重复写入。
-- 4. 商户端当前页面：经营概览、垫资池管理、优惠规则、结算订单、提现结算；预警设置和提现记录页为带参数或入口页内跳转页面，不作为独立左侧菜单展示。
-- 5. route 字段不要包含 /merchant 前缀，后端 merchantMenus 会自动拼接。

INSERT IGNORE INTO `eb_system_menu`
(`menu_id`,`pid`,`path`,`icon`,`menu_name`,`route`,`params`,`sort`,`is_show`,`is_mer`,`is_menu`,`create_time`,`update_time`,`is_agent`)
VALUES
(20018,0,'/','s-data','惠买单','/huimaidan/dashboard','[]',60,1,1,1,NOW(),NOW(),0),
(20019,20018,'/20018/','','经营概览','/huimaidan/dashboard','[]',1,1,1,1,NOW(),NOW(),0),
(20020,20018,'/20018/','','垫资池管理','/huimaidan/pool','[]',2,1,1,1,NOW(),NOW(),0),
(20021,20020,'/20018/20020/','','预警设置','/huimaidan/pool/alarm/:id','[]',1,0,1,1,NOW(),NOW(),0),
(20022,20019,'/20018/20019/','','概览数据','merchantHuimaidanDashboardOverview','',1,1,1,0,NOW(),NOW(),0),
(20023,20020,'/20018/20020/','','垫资池信息','merchantHuimaidanPoolInfo','',1,1,1,0,NOW(),NOW(),0),
(20024,20020,'/20018/20020/','','预警设置','merchantHuimaidanPoolAlarm','',2,1,1,0,NOW(),NOW(),0),
(20025,20020,'/20018/20020/','','流水列表','merchantHuimaidanPoolTransactions','',3,1,1,0,NOW(),NOW(),0),
(20026,20020,'/20018/20020/','','导出流水','merchantHuimaidanPoolTransactionsExport','',4,1,1,0,NOW(),NOW(),0),
(20030,20018,'/20018/','','优惠规则','/huimaidan/discount','[]',3,1,1,1,NOW(),NOW(),0),
(20031,20030,'/20018/20030/','','新增规则','/huimaidan/discount/create','[]',1,0,1,1,NOW(),NOW(),0),
(20032,20030,'/20018/20030/','','编辑规则','/huimaidan/discount/create/:id','[]',2,0,1,1,NOW(),NOW(),0),
(20033,20030,'/20018/20030/','','优惠规则列表','merchantHuimaidanDiscountLst','',1,1,1,0,NOW(),NOW(),0),
(20034,20030,'/20018/20030/','','添加优惠规则','merchantHuimaidanDiscountCreate','',2,1,1,0,NOW(),NOW(),0),
(20035,20030,'/20018/20030/','','编辑优惠规则','merchantHuimaidanDiscountUpdate','',3,1,1,0,NOW(),NOW(),0),
(20036,20030,'/20018/20030/','','优惠规则状态','merchantHuimaidanDiscountStatus','',4,1,1,0,NOW(),NOW(),0),
(20037,20030,'/20018/20030/','','删除优惠规则','merchantHuimaidanDiscountDelete','',5,1,1,0,NOW(),NOW(),0),
(20038,20018,'/20018/','','结算订单','/huimaidan/settlement','[]',4,1,1,1,NOW(),NOW(),0),
(20039,20038,'/20018/20038/','','结算统计','merchantHuimaidanSettlementStats','',1,1,1,0,NOW(),NOW(),0),
(20040,20038,'/20018/20038/','','订单列表','merchantHuimaidanSettlementOrders','',2,1,1,0,NOW(),NOW(),0),
(20041,20038,'/20018/20038/','','订单详情','merchantHuimaidanSettlementOrderDetail','',3,1,1,0,NOW(),NOW(),0),
(20048,20018,'/20018/','','提现结算','/huimaidan/withdraw','[]',5,1,1,1,NOW(),NOW(),0),
(20049,20048,'/20018/20048/','','提现概览','merchantHuimaidanWithdrawOverview','',1,1,1,0,NOW(),NOW(),0),
(20050,20048,'/20018/20048/','','当前申请','merchantHuimaidanWithdrawCurrent','',2,1,1,0,NOW(),NOW(),0),
(20051,20048,'/20018/20048/','','提现记录','merchantHuimaidanWithdrawRecords','',3,1,1,0,NOW(),NOW(),0),
(20052,20048,'/20018/20048/','','保存收款码','merchantHuimaidanWithdrawAccount','',4,1,1,0,NOW(),NOW(),0),
(20053,20048,'/20018/20048/','','提交提现','merchantHuimaidanWithdrawApply','',5,1,1,0,NOW(),NOW(),0),
(20130,20018,'/20018/','','财务概览','merchantHuimaidanFinanceOverview','',7,1,1,0,NOW(),NOW(),0),
(20131,20018,'/20018/','','销售额度','merchantHuimaidanFinanceQuota','',8,1,1,0,NOW(),NOW(),0),
(20132,20018,'/20018/','','余额明细','merchantHuimaidanFinanceRecords','',9,1,1,0,NOW(),NOW(),0);

INSERT INTO `eb_relevance` (`left_id`,`right_id`,`type`)
SELECT mt.mer_type_id, t.right_id, 'mer_auth'
FROM `eb_merchant_type` mt
JOIN (
    SELECT 20018 AS right_id
    UNION ALL SELECT 20019
    UNION ALL SELECT 20020
    UNION ALL SELECT 20021
    UNION ALL SELECT 20022
    UNION ALL SELECT 20023
    UNION ALL SELECT 20024
    UNION ALL SELECT 20025
    UNION ALL SELECT 20026
    UNION ALL SELECT 20030
    UNION ALL SELECT 20031
    UNION ALL SELECT 20032
    UNION ALL SELECT 20033
    UNION ALL SELECT 20034
    UNION ALL SELECT 20035
    UNION ALL SELECT 20036
    UNION ALL SELECT 20037
    UNION ALL SELECT 20038
    UNION ALL SELECT 20039
    UNION ALL SELECT 20040
    UNION ALL SELECT 20041
    UNION ALL SELECT 20048
    UNION ALL SELECT 20049
    UNION ALL SELECT 20050
    UNION ALL SELECT 20051
    UNION ALL SELECT 20052
    UNION ALL SELECT 20053
    UNION ALL SELECT 20130
    UNION ALL SELECT 20131
    UNION ALL SELECT 20132
) AS t
WHERE NOT EXISTS (
    SELECT 1
    FROM `eb_relevance` r
    WHERE r.left_id = mt.mer_type_id
      AND r.right_id = t.right_id
      AND r.type = 'mer_auth'
);

-- 修复已执行过旧版 INSERT IGNORE 的环境：INSERT IGNORE 不会更新已存在的 menu_id。
UPDATE `eb_system_menu`
SET `route` = '/huimaidan/withdraw',
    `update_time` = NOW()
WHERE `menu_id` = 20048
  AND `is_mer` = 1;
