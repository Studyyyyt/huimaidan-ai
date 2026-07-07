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

namespace app\controller\merchant\huimaidan;

use app\common\repositories\system\merchant\FinancialRecordRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use app\common\repositories\store\order\StoreOrderRepository;
use app\common\repositories\store\order\StoreRefundOrderRepository;
use crmeb\basic\BaseController;
use think\App;

/**
 * 商户财务信息
 * Class Finance
 * @package app\controller\merchant\huimaidan
 */
class Finance extends BaseController
{
    protected $financialRecordRepository;
    protected $merchantRepository;
    protected $storeOrderRepository;
    protected $storeRefundOrderRepository;

    public function __construct(App $app, FinancialRecordRepository $financialRecordRepository, MerchantRepository $merchantRepository, StoreOrderRepository $storeOrderRepository, StoreRefundOrderRepository $storeRefundOrderRepository)
    {
        parent::__construct($app);
        $this->financialRecordRepository = $financialRecordRepository;
        $this->merchantRepository = $merchantRepository;
        $this->storeOrderRepository = $storeOrderRepository;
        $this->storeRefundOrderRepository = $storeRefundOrderRepository;
    }

    /**
     * 财务概览 - 顶部统计卡片
     * GET /mer/huimaidan/finance/overview
     *
     * @return \think\response\Json
     */
    public function overview()
    {
        $merId = $this->request->merId();
        if (!$merId) {
            return app('json')->fail('商户不存在');
        }

        // 获取当前日期范围
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // 从订单表查询收款数据（更准确）
        // 基础查询条件：已支付、未删除的惠买单订单
        $baseQuery = $this->storeOrderRepository->search(['mer_id' => $merId])
            ->where('eb_store_order.paid', 1)
            ->where('eb_store_order.is_del', 0);

        // 累计收款（所有历史已支付订单）
        $totalReceived = (float)$baseQuery->sum('eb_store_order.pay_price');

        // 昨日新增收款
        $yesterdayReceived = (float)(clone $baseQuery)
            ->whereDay('eb_store_order.pay_time', $yesterday)
            ->sum('eb_store_order.pay_price');

        // 本月新增收款
        $monthReceived = (float)(clone $baseQuery)
            ->whereBetweenTime('eb_store_order.pay_time', $monthStart, $monthEnd)
            ->sum('eb_store_order.pay_price');

        // 今日新增收款
        $todayReceived = (float)(clone $baseQuery)
            ->whereDay('eb_store_order.pay_time', $today)
            ->sum('eb_store_order.pay_price');

        // 退款数据仍从财务记录表查询
        $refundTypes = ['refund_order'];

        // 累计退款
        $totalRefund = $this->financialRecordRepository->search(['is_mer' => $merId])
            ->where('financial_type', 'in', $refundTypes)
            ->sum('number');

        // 昨日新增退款
        $yesterdayRefund = $this->financialRecordRepository->search(['is_mer' => $merId])
            ->where('financial_type', 'in', $refundTypes)
            ->whereDay('create_time', $yesterday)
            ->sum('number');

        // 本月新增退款
        $monthRefund = $this->financialRecordRepository->search(['is_mer' => $merId])
            ->where('financial_type', 'in', $refundTypes)
            ->whereBetweenTime('create_time', $monthStart, $monthEnd)
            ->sum('number');

        // 今日新增退款
        $todayRefund = $this->financialRecordRepository->search(['is_mer' => $merId])
            ->where('financial_type', 'in', $refundTypes)
            ->whereDay('create_time', $today)
            ->sum('number');

        // 今日订单数
        $todayOrderCount = $this->storeOrderRepository->search(['mer_id' => $merId])
            ->whereDay('eb_store_order.create_time', $today)
            ->count();

        // 退款订单数（从退款订单表查询）
        $refundOrderCount = $this->storeRefundOrderRepository->getWhereCount([
            'mer_id' => $merId,
            'is_system_del' => 0,
        ]);

        // 全部订单数（已支付）
        $allOrderCount = $this->storeOrderRepository->search(['mer_id' => $merId])
            ->where('eb_store_order.is_del', 0)
            ->where('eb_store_order.paid', 1)
            ->count();

        $data = [
            'totalReceived' => (float)$totalReceived,
            'yesterdayReceived' => (float)$yesterdayReceived,
            'monthReceived' => (float)$monthReceived,
            'todayReceived' => (float)$todayReceived,
            'totalRefund' => (float)$totalRefund,
            'yesterdayRefund' => (float)$yesterdayRefund,
            'monthRefund' => (float)$monthRefund,
            'todayRefund' => (float)$todayRefund,
            'todayOrderCount' => (int)$todayOrderCount,
            'refundOrderCount' => (int)$refundOrderCount,
            'allOrderCount' => (int)$allOrderCount,
        ];

        return app('json')->success($data);
    }

    /**
     * 销售额度信息
     * GET /mer/huimaidan/finance/quota
     *
     * @return \think\response\Json
     */
    public function quota()
    {
        $merId = $this->request->merId();
        if (!$merId) {
            return app('json')->fail('商户不存在');
        }

        // 获取商户信息
        $merchant = $this->merchantRepository->search(['mer_id' => $merId])
            ->field('mer_id,mer_money')
            ->find();

        if (!$merchant) {
            return app('json')->fail('商户不存在');
        }

        // 计算已销售额度（累计收款金额）
        $incomeTypes = ['order', 'mer_presell'];
        $salesQuota = (float)$this->financialRecordRepository->search(['is_mer' => $merId])
            ->where('financial_type', 'in', $incomeTypes)
            ->sum('number');

        // 总额度 - 如果商户没有设置，默认为0表示无限制
        // 注意：需要在merchant表添加 huimaidan_total_quota 字段才能使用此功能
        // 目前暂时返回0，表示无限制
        $totalQuota = 0;

        $data = [
            'salesQuota' => $salesQuota,
            'totalQuota' => $totalQuota,
        ];

        return app('json')->success($data);
    }

    /**
     * 余额明细列表
     * GET /mer/huimaidan/finance/records
     *
     * @return \think\response\Json
     */
    public function records()
    {
        $merId = $this->request->merId();
        if (!$merId) {
            return app('json')->fail('商户不存在');
        }

        [$page, $limit] = $this->getPage();

        // 构建查询条件
        $where = [
            'is_mer' => $merId,
            'financial_type' => ['order', 'mer_presell', 'refund_order', 'brokerage_one', 'refund_brokerage_one', 'order_charge', 'presell_charge', 'refund_charge', 'order_platform_coupon', 'order_svip_coupon', 'refund_platform_coupon', 'refund_svip_coupon'],
        ];

        // 支持按类型筛选
        $type = $this->request->param('type', '');
        if ($type === 'income') {
            $where['financial_pm'] = 1;
        } elseif ($type === 'expense') {
            $where['financial_pm'] = 0;
        }

        // 支持按日期筛选
        $date = $this->request->param('date', '');
        if ($date) {
            $where['date'] = $date;
        }

        // 支持关键词搜索
        $keyword = $this->request->param('keyword', '');
        if ($keyword) {
            $where['keyword'] = $keyword;
        }

        $result = $this->financialRecordRepository->getList($where, $page, $limit);

        // 格式化返回数据，适配前端需求
        $list = [];
        foreach ($result['list'] as $item) {
            $list[] = [
                'id' => $item->financial_record_id,
                'type' => $item->financial_pm == 1 ? 'income' : 'expense',
                'amount' => (float)$item->number,
                'balance' => 0, // 需要根据业务逻辑计算，暂时返回0
                'mark' => $item->title ?? '交易',
                'create_time' => $item->create_time,
            ];
        }

        return app('json')->success([
            'list' => $list,
            'count' => $result['count'],
        ]);
    }
}
