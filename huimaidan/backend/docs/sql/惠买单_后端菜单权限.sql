-- 惠买单后端菜单权限 SQL
-- 说明：
-- 1. 仅补充惠买单平台后台的菜单与权限节点。
-- 2. 使用固定 menu_id，便于重复执行时依靠主键避免重复写入。
-- 3. 当前仓库已存在的后端路由：垫资池管理、结算统计、订单明细。
-- 4. 如果后续新增详情页或更多后端路由，再继续补充对应权限节点。

INSERT IGNORE INTO `eb_system_menu`
(`menu_id`,`pid`,`path`,`icon`,`menu_name`,`route`,`params`,`sort`,`is_show`,`is_mer`,`is_menu`,`create_time`,`update_time`,`is_agent`)
VALUES
(20000,0,'/','s-data','惠买单','/huimaidan','[]',60,1,0,1,NOW(),NOW(),0),
(20001,20000,'/20000/','','垫资池管理','/huimaidan/pool','[]',1,1,0,1,NOW(),NOW(),0),
(20002,20001,'/20000/20001/','','列表','adminHuimaidanPoolLst','',1,1,0,2,NOW(),NOW(),0),
(20003,20001,'/20000/20001/','','初始化','adminHuimaidanPoolCreate','',2,1,0,2,NOW(),NOW(),0),
(20004,20001,'/20000/20001/','','详情','adminHuimaidanPoolDetail','',3,1,0,2,NOW(),NOW(),0),
(20005,20001,'/20000/20001/','','充值','adminHuimaidanPoolRecharge','',4,1,0,2,NOW(),NOW(),0),
(20006,20001,'/20000/20001/','','调整','adminHuimaidanPoolAdjust','',5,1,0,2,NOW(),NOW(),0),
(20007,20001,'/20000/20001/','','预警设置','adminHuimaidanPoolAlarm','',6,1,0,2,NOW(),NOW(),0),
(20008,20001,'/20000/20001/','','状态','adminHuimaidanPoolStatus','',7,1,0,2,NOW(),NOW(),0),
(20009,20001,'/20000/20001/','','流水','adminHuimaidanPoolTransactions','',8,1,0,2,NOW(),NOW(),0),
(20010,20001,'/20000/20001/','','预警列表','adminHuimaidanPoolAlarms','',9,1,0,2,NOW(),NOW(),0),
(20011,20001,'/20000/20001/','','预警历史','adminHuimaidanPoolAlarmRecords','',10,1,0,2,NOW(),NOW(),0),
(20012,20001,'/20000/20001/','','批量预警','adminHuimaidanPoolBatchAlarm','',11,1,0,2,NOW(),NOW(),0),
(20013,20000,'/20000/','','结算统计','/huimaidan/settlement','[]',2,1,0,1,NOW(),NOW(),0),
(20014,20013,'/20000/20013/','','统计','adminHuimaidanSettlementStats','',1,1,0,2,NOW(),NOW(),0),
(20015,20000,'/20000/','','订单明细','/huimaidan/settlement/orders','[]',3,1,0,1,NOW(),NOW(),0),
(20016,20015,'/20000/20015/','','列表','adminHuimaidanSettlementOrders','',1,1,0,2,NOW(),NOW(),0),
(20017,20015,'/20000/20015/','','导出','adminHuimaidanSettlementOrdersExport','',2,1,0,2,NOW(),NOW(),0),
(20042,20000,'/20000/','','提现审核','/huimaidan/withdraw','[]',4,1,0,1,NOW(),NOW(),0),
(20043,20042,'/20000/20042/','','列表','adminHuimaidanWithdrawLst','',1,1,0,2,NOW(),NOW(),0),
(20044,20042,'/20000/20042/','','详情','adminHuimaidanWithdrawDetail','',2,1,0,2,NOW(),NOW(),0),
(20045,20042,'/20000/20042/','','审核','adminHuimaidanWithdrawAudit','',3,1,0,2,NOW(),NOW(),0),
(20046,20042,'/20000/20042/','','打款凭证','adminHuimaidanWithdrawTransfer','',4,1,0,2,NOW(),NOW(),0),
(20047,20042,'/20000/20042/','','统计','adminHuimaidanWithdrawStats','',5,1,0,2,NOW(),NOW(),0);
