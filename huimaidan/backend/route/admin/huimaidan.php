<?php

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

use app\common\middleware\AdminAuthMiddleware;
use app\common\middleware\AdminTokenMiddleware;
use app\common\middleware\AllowOriginMiddleware;
use app\common\middleware\LogMiddleware;
use think\facade\Route;

Route::group(function () {
    Route::group('huimaidan/pool', function () {
        Route::get('lst', 'Pool/lst')->name('adminHuimaidanPoolLst')->option(['_alias' => '垫资池列表']);
        Route::post('create', 'Pool/create')->name('adminHuimaidanPoolCreate')->option(['_alias' => '初始化垫资池']);
        Route::get('detail/:id', 'Pool/detail')->name('adminHuimaidanPoolDetail')->option(['_alias' => '垫资池详情']);
        Route::post('recharge/:id', 'Pool/recharge')->name('adminHuimaidanPoolRecharge')->option(['_alias' => '垫资池充值']);
        Route::post('adjust/:id', 'Pool/adjust')->name('adminHuimaidanPoolAdjust')->option(['_alias' => '垫资池调整']);
        Route::post('alarm/:id', 'Pool/alarm')->name('adminHuimaidanPoolAlarm')->option(['_alias' => '预警设置']);
        Route::post('status/:id', 'Pool/status')->name('adminHuimaidanPoolStatus')->option(['_alias' => '状态设置']);
        Route::get('transactions/:id', 'Pool/transactions')->name('adminHuimaidanPoolTransactions')->option(['_alias' => '垫资池流水']);
        Route::get('alarms', 'Pool/alarms')->name('adminHuimaidanPoolAlarms')->option(['_alias' => '垫资池预警列表']);
        Route::get('alarm_records', 'Pool/alarmRecords')->name('adminHuimaidanPoolAlarmRecords')->option(['_alias' => '垫资池预警历史']);
        Route::post('batch_alarm', 'Pool/batchAlarm')->name('adminHuimaidanPoolBatchAlarm')->option(['_alias' => '批量设置垫资池预警']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/pool',
        '_auth' => true,
    ]);

    Route::group('huimaidan/settlement', function () {
        Route::get('stats', 'Settlement/stats')->name('adminHuimaidanSettlementStats')->option(['_alias' => '差价统计']);
        Route::get('orders/export', 'Settlement/ordersExport')->name('adminHuimaidanSettlementOrdersExport')->option(['_alias' => '导出买单订单']);
        Route::get('merchants/export', 'Settlement/merchantsExport')->name('adminHuimaidanSettlementMerchantsExport')->option(['_alias' => '导出商户汇总']);
        Route::get('orders', 'Settlement/orders')->name('adminHuimaidanSettlementOrders')->option(['_alias' => '买单订单']);
        Route::get('merchants', 'Settlement/merchants')->name('adminHuimaidanSettlementMerchants')->option(['_alias' => '商户汇总']);
        Route::get('daily', 'Settlement/daily')->name('adminHuimaidanSettlementDaily')->option(['_alias' => '差价日趋势']);
        Route::get('month_compare', 'Settlement/monthCompare')->name('adminHuimaidanSettlementMonthCompare')->option(['_alias' => '差价月度对比']);
        Route::get('rank', 'Settlement/rank')->name('adminHuimaidanSettlementRank')->option(['_alias' => '商户差价排行']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/settlement',
        '_auth' => true,
    ]);

    Route::group('huimaidan/discount', function () {
        Route::get('config', 'Discount/config')->name('adminHuimaidanDiscountConfig')->option(['_alias' => '惠买单折扣全局配置']);
        Route::get('lst', 'Discount/lst')->name('adminHuimaidanDiscountLst')->option(['_alias' => '惠买单折扣配置列表']);
        Route::get('detail/:id', 'Discount/detail')->name('adminHuimaidanDiscountDetail')->option(['_alias' => '惠买单折扣配置详情']);
        Route::post('create', 'Discount/create')->name('adminHuimaidanDiscountCreate')->option(['_alias' => '新增惠买单折扣配置']);
        Route::post('update/:id', 'Discount/update')->name('adminHuimaidanDiscountUpdate')->option(['_alias' => '编辑惠买单折扣配置']);
        Route::post('status/:id', 'Discount/status')->name('adminHuimaidanDiscountStatus')->option(['_alias' => '启停惠买单折扣配置']);
        Route::delete('delete/:id', 'Discount/delete')->name('adminHuimaidanDiscountDelete')->option(['_alias' => '删除惠买单折扣配置']);
        Route::get('member_levels', 'Discount/memberLevels')->name('adminHuimaidanDiscountMemberLevels')->option(['_alias' => '惠买单可配置用户等级']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/discount',
        '_auth' => true,
    ]);

    Route::group('huimaidan/store_qrcode', function () {
        Route::get('lst', 'StoreQrcode/lst')->name('adminHuimaidanStoreQrcodeLst')->option(['_alias' => '店铺二维码列表']);
        Route::get('detail/:mer_id', 'StoreQrcode/detail')->name('adminHuimaidanStoreQrcodeDetail')->option(['_alias' => '店铺二维码详情']);
        Route::post('refresh/:mer_id', 'StoreQrcode/refresh')->name('adminHuimaidanStoreQrcodeRefresh')->option(['_alias' => '强制刷新店铺二维码']);
        Route::get('download/:mer_id', 'StoreQrcode/download')->name('adminHuimaidanStoreQrcodeDownload')->option(['_alias' => '下载店铺二维码']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/store-qrcode',
        '_auth' => true,
    ]);

    Route::group('huimaidan/withdraw', function () {
        Route::get('lst', 'Withdraw/lst')->name('adminHuimaidanWithdrawLst')->option(['_alias' => '惠买单提现申请列表']);
        Route::get('detail/:id', 'Withdraw/detail')->name('adminHuimaidanWithdrawDetail')->option(['_alias' => '惠买单提现详情']);
        Route::post('audit/:id', 'Withdraw/audit')->name('adminHuimaidanWithdrawAudit')->option(['_alias' => '审核惠买单提现']);
        Route::post('transfer/:id', 'Withdraw/transfer')->name('adminHuimaidanWithdrawTransfer')->option(['_alias' => '惠买单打款凭证']);
        Route::get('stats', 'Withdraw/stats')->name('adminHuimaidanWithdrawStats')->option(['_alias' => '惠买单提现统计']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/withdraw',
        '_auth' => true,
    ]);

    Route::group('huimaidan/ai', function () {
        Route::get('tags', 'Ai/tags')->name('adminHuimaidanAiTags')->option(['_alias' => 'AI标签列表']);
        Route::post('tag/save', 'Ai/saveTag')->name('adminHuimaidanAiTagSave')->option(['_alias' => '保存AI标签']);
        Route::post('tag/import', 'Ai/importTags')->name('adminHuimaidanAiTagImport')->option(['_alias' => '批量导入AI标签']);
        Route::delete('tag/delete/:id', 'Ai/deleteTag')->name('adminHuimaidanAiTagDelete')->option(['_alias' => '删除AI标签']);
        Route::get('merchants', 'Ai/merchants')->name('adminHuimaidanAiMerchants')->option(['_alias' => 'AI商户选择列表']);
        Route::delete('merchant/:merId', 'Ai/deleteMerchant')->name('adminHuimaidanAiMerchantDelete')->option(['_alias' => '删除AI推荐商户']);
        Route::get('merchant_profile/:merId', 'Ai/merchantProfile')->name('adminHuimaidanAiMerchantProfile')->option(['_alias' => '商户AI展示资料']);
        Route::post('merchant_profile/:merId', 'Ai/saveMerchantProfile')->name('adminHuimaidanAiMerchantProfileSave')->option(['_alias' => '保存商户AI展示资料']);
        Route::post('merchant_tags/init', 'Ai/initMerchantTags')->name('adminHuimaidanAiMerchantTagsInit')->option(['_alias' => '初始化商户AI标签']);
        Route::get('merchant_tags/suggest/:merId', 'Ai/suggestMerchantTags')->name('adminHuimaidanAiMerchantTagsSuggest')->option(['_alias' => '推荐商户AI标签']);
        Route::get('merchant_tags/:merId', 'Ai/merchantTags')->name('adminHuimaidanAiMerchantTags')->option(['_alias' => '商户AI标签']);
        Route::post('merchant_tags/:merId', 'Ai/saveMerchantTags')->name('adminHuimaidanAiMerchantTagsSave')->option(['_alias' => '保存商户AI标签']);
        Route::get('merchant_health/:merId', 'Ai/merchantHealth')->name('adminHuimaidanAiMerchantHealth')->option(['_alias' => '商户AI健康检查']);
        Route::get('merchant_health', 'Ai/merchantHealthList')->name('adminHuimaidanAiMerchantHealthList')->option(['_alias' => '商户AI健康检查列表']);
        Route::get('banners', 'Ai/banners')->name('adminHuimaidanAiBanners')->option(['_alias' => 'AI Banner配置']);
        Route::post('banner/save', 'Ai/saveBanner')->name('adminHuimaidanAiBannerSave')->option(['_alias' => '保存AI Banner配置']);
        Route::delete('banner/delete/:id', 'Ai/deleteBanner')->name('adminHuimaidanAiBannerDelete')->option(['_alias' => '删除AI Banner配置']);
        Route::get('configs', 'Ai/configs')->name('adminHuimaidanAiConfigs')->option(['_alias' => 'AI推荐参数']);
        Route::post('config/save', 'Ai/saveConfig')->name('adminHuimaidanAiConfigSave')->option(['_alias' => '保存AI推荐参数']);
        Route::delete('config/delete/:id', 'Ai/deleteConfig')->name('adminHuimaidanAiConfigDelete')->option(['_alias' => '删除AI推荐参数']);
        Route::post('test_connection', 'Ai/testConnection')->name('adminHuimaidanAiTestConnection')->option(['_alias' => '测试AI模型连接']);
        Route::post('reset_circuit_breaker', 'Ai/resetCircuitBreaker')->name('adminHuimaidanAiResetCircuitBreaker')->option(['_alias' => '重置AI服务熔断状态']);
        Route::post('simulate', 'Ai/simulate')->name('adminHuimaidanAiSimulate')->option(['_alias' => 'AI推荐模拟测试']);
        Route::get('status', 'Ai/aiStatus')->name('adminHuimaidanAiStatus')->option(['_alias' => 'AI推荐上线检查']);
        Route::get('logs', 'Ai/logs')->name('adminHuimaidanAiLogs')->option(['_alias' => 'AI推荐日志']);
        Route::get('logs/summary', 'Ai/logsSummary')->name('adminHuimaidanAiLogsSummary')->option(['_alias' => 'AI推荐日志聚合统计']);
        Route::get('onboarding_config', 'Ai/onboardingConfig')->name('adminHuimaidanAiOnboardingConfig')->option(['_alias' => 'AI新手引导配置']);
        Route::post('onboarding_config', 'Ai/saveOnboardingConfig')->name('adminHuimaidanAiOnboardingConfigSave')->option(['_alias' => '保存AI新手引导配置']);
        Route::get('merchant_import/template', 'Ai/merchantImportTemplate')->name('adminHuimaidanAiMerchantImportTemplate')->option(['_alias' => '下载AI商户导入模板']);
        Route::post('merchant_import', 'Ai/merchantImport')->name('adminHuimaidanAiMerchantImport')->option(['_alias' => '导入AI推荐商户']);
    })->prefix('admin.huimaidan.')->option([
        '_path' => '/huimaidan/ai',
        '_auth' => true,
    ]);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(AdminTokenMiddleware::class, true)
    ->middleware(AdminAuthMiddleware::class)
    ->middleware(LogMiddleware::class);
