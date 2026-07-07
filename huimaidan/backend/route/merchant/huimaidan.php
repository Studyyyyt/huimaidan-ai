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

use app\common\middleware\AllowOriginMiddleware;
use app\common\middleware\LogMiddleware;
use app\common\middleware\MerchantAuthMiddleware;
use app\common\middleware\MerchantCheckBaseInfoMiddleware;
use app\common\middleware\MerchantTokenMiddleware;
use think\facade\Route;

Route::group(function () {
    Route::group('huimaidan/pool', function () {
        Route::get('info', 'Pool/info')->name('merchantHuimaidanPoolInfo')->option(['_alias' => '垫资池信息']);
        Route::post('alarm/:id', 'Pool/alarm')->name('merchantHuimaidanPoolAlarm')->option(['_alias' => '预警设置']);
        Route::get('transactions/:id/export', 'Pool/transactionsExport')->name('merchantHuimaidanPoolTransactionsExport')->option(['_alias' => '导出垫资池流水']);
        Route::get('transactions/:id', 'Pool/transactions')->name('merchantHuimaidanPoolTransactions')->option(['_alias' => '垫资池流水']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/pool',
        '_auth' => true,
    ]);

    Route::group('huimaidan/discount', function () {
        Route::get('lst', 'Discount/lst')->name('merchantHuimaidanDiscountLst')->option(['_alias' => '优惠规则列表']);
        Route::post('create', 'Discount/create')->name('merchantHuimaidanDiscountCreate')->option(['_alias' => '添加优惠规则']);
        Route::post('update/:id', 'Discount/update')->name('merchantHuimaidanDiscountUpdate')->option(['_alias' => '编辑优惠规则']);
        Route::post('status/:id', 'Discount/status')->name('merchantHuimaidanDiscountStatus')->option(['_alias' => '优惠规则状态']);
        Route::delete('delete/:id', 'Discount/delete')->name('merchantHuimaidanDiscountDelete')->option(['_alias' => '删除优惠规则']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/discount',
        '_auth' => true,
    ]);

    Route::group('huimaidan/dashboard', function () {
        Route::get('overview', 'Dashboard/overview')->name('merchantHuimaidanDashboardOverview')->option(['_alias' => '惠买单经营概览']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/dashboard',
        '_auth' => true,
    ]);

    Route::group('huimaidan/store_qrcode', function () {
        Route::get('detail', 'StoreQrcode/detail')->name('merchantHuimaidanStoreQrcodeDetail')->option(['_alias' => '店铺二维码详情']);
        Route::post('refresh', 'StoreQrcode/refresh')->name('merchantHuimaidanStoreQrcodeRefresh')->option(['_alias' => '刷新店铺二维码']);
        Route::get('download', 'StoreQrcode/download')->name('merchantHuimaidanStoreQrcodeDownload')->option(['_alias' => '下载店铺二维码']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/store-qrcode',
        '_auth' => true,
    ]);

    Route::group('huimaidan/voice_device', function () {
        Route::get('lst', 'VoiceDevice/lst')->name('merchantHuimaidanVoiceDeviceLst')->option(['_alias' => '语音播报设备列表']);
        Route::get('detail', 'VoiceDevice/detail')->name('merchantHuimaidanVoiceDeviceDetail')->option(['_alias' => '语音播报设备详情']);
        Route::post('create', 'VoiceDevice/create')->name('merchantHuimaidanVoiceDeviceCreate')->option(['_alias' => '绑定语音播报设备']);
        Route::post('update', 'VoiceDevice/update')->name('merchantHuimaidanVoiceDeviceUpdate')->option(['_alias' => '编辑语音播报设备']);
        Route::post('delete', 'VoiceDevice/delete')->name('merchantHuimaidanVoiceDeviceDelete')->option(['_alias' => '删除语音播报设备']);
        Route::post('changeStatus', 'VoiceDevice/changeStatus')->name('merchantHuimaidanVoiceDeviceChangeStatus')->option(['_alias' => '切换语音播报设备状态']);
        Route::post('testPush', 'VoiceDevice/testPush')->name('merchantHuimaidanVoiceDeviceTestPush')->option(['_alias' => '测试语音播报']);
        Route::get('pushLog', 'VoiceDevice/pushLog')->name('merchantHuimaidanVoiceDevicePushLog')->option(['_alias' => '语音播报日志']);
        Route::get('statistics', 'VoiceDevice/statistics')->name('merchantHuimaidanVoiceDeviceStatistics')->option(['_alias' => '语音播报统计']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/voice_device',
        '_auth' => true,
    ]);

    Route::group('huimaidan/settlement', function () {
        Route::get('stats', 'Settlement/stats')->name('merchantHuimaidanSettlementStats')->option(['_alias' => '惠买单结算统计']);
        Route::get('hourly', 'Settlement/hourly')->name('merchantHuimaidanSettlementHourly')->option(['_alias' => '惠买单小时级收款趋势']);
        Route::get('orders', 'Settlement/orders')->name('merchantHuimaidanSettlementOrders')->option(['_alias' => '惠买单订单列表']);
        Route::get('order/:id', 'Settlement/detail')->name('merchantHuimaidanSettlementOrderDetail')->option(['_alias' => '惠买单订单详情']);
        Route::get('overview', 'Withdraw/overview')->name('merchantHuimaidanWithdrawOverview')->option(['_alias' => '惠买单提现概览']);
        Route::get('withdraw/current', 'Withdraw/current')->name('merchantHuimaidanWithdrawCurrent')->option(['_alias' => '当前提现申请']);
        Route::get('withdraw/records', 'Withdraw/records')->name('merchantHuimaidanWithdrawRecords')->option(['_alias' => '惠买单提现记录']);
        Route::get('withdraw/list', 'Withdraw/list')->name('merchantHuimaidanWithdrawList')->option(['_alias' => '提现记录列表']);
        Route::post('withdraw/account', 'Withdraw/account')->name('merchantHuimaidanWithdrawAccount')->option(['_alias' => '保存惠买单收款码']);
        Route::post('withdraw/apply', 'Withdraw/apply')->name('merchantHuimaidanWithdrawApply')->option(['_alias' => '提交惠买单提现']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/settlement',
        '_auth' => true,
    ]);

    Route::group('huimaidan/finance', function () {
        Route::get('overview', 'Finance/overview')->name('merchantHuimaidanFinanceOverview')->option(['_alias' => '财务概览统计']);
        Route::get('quota', 'Finance/quota')->name('merchantHuimaidanFinanceQuota')->option(['_alias' => '销售额度信息']);
        Route::get('records', 'Finance/records')->name('merchantHuimaidanFinanceRecords')->option(['_alias' => '余额明细列表']);
    })->prefix('merchant.huimaidan.')->option([
        '_path' => '/huimaidan/finance',
        '_auth' => true,
    ]);
})->middleware(AllowOriginMiddleware::class)
    ->middleware(MerchantTokenMiddleware::class, true)
    ->middleware(MerchantAuthMiddleware::class)
    ->middleware(MerchantCheckBaseInfoMiddleware::class)
    ->middleware(LogMiddleware::class);
