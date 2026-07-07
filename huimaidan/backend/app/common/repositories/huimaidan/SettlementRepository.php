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

namespace app\common\repositories\huimaidan;

use app\common\dao\huimaidan\DiscountRuleDao;
use app\common\dao\system\financial\FinancialDao;
use app\common\model\huimaidan\CapitalPool;
use app\common\model\store\order\StoreOrder;
use app\common\repositories\system\merchant\MerchantRepository;
use think\exception\ValidateException;
use think\facade\Db;

class SettlementRepository
{
    protected $calculator;
    protected $discountRuleDao;
    protected $poolRepository;
    protected $poolRulePolicy;

    public function __construct(
        SettlementCalculator $calculator,
        DiscountRuleDao $discountRuleDao,
        PoolRepository $poolRepository,
        PoolRulePolicy $poolRulePolicy
    ) {
        $this->calculator = $calculator;
        $this->discountRuleDao = $discountRuleDao;
        $this->poolRepository = $poolRepository;
        $this->poolRulePolicy = $poolRulePolicy;
    }

    public function stats(array $where): array
    {
        $query = $this->baseQuery($where);
        $today = date('Y-m-d 00:00:00');
        $todayQuery = $this->baseQuery($where)->where('pay_time', '>=', $today);
        return [
            'order_count' => (int)$query->count(),
            'pay_amount' => $this->money($this->baseQuery($where)->sum('pay_price')),
            'merchant_cost_amount' => $this->money($this->baseQuery($where)->sum('merchant_cost_amount')),
            'platform_profit' => $this->money($this->baseQuery($where)->sum('platform_profit')),
            'pool_platform_profit' => $this->money($this->baseQuery($where)->where('settlement_mode', MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL)->sum('platform_profit')),
            'withdraw_income_amount' => $this->money($this->baseQuery($where)->where('settlement_mode', MerchantRepository::HUIMAIDAN_SETTLEMENT_WITHDRAW)->sum('pay_price')),
            'withdraw_fee_amount' => $this->money($this->withdrawFeeQuery($where)->sum('fee_amount')),
            // 经营概览 Dashboard 字段
            'pool_count' => (int)CapitalPool::getDB()->count(),
            'alarm_count' => (int)CapitalPool::getDB()->where('alarm_enabled', 1)->whereColumn('balance', '<=', 'alarm_balance')->count(),
            'today_orders' => (int)$todayQuery->count(),
            'today_profit' => $this->money($todayQuery->sum('platform_profit')),
        ];
    }

    public function orderList(array $where, $page, $limit)
    {
        $query = $this->baseQuery($where)->with(['merchant', 'user'])->order('order_id DESC');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        $stat = $this->stats($where);
        return compact('count', 'list', 'stat');
    }

    public function merchantStats(array $where, $page, $limit)
    {
        $count = count($this->baseQuery($where)->group('mer_id')->column('mer_id'));
        $query = $this->baseQuery($where)->field('mer_id,count(order_id) order_count,sum(pay_price) pay_amount,sum(merchant_cost_amount) merchant_cost_amount,sum(platform_profit) platform_profit')
            ->with(['merchant'])->group('mer_id')->order('platform_profit DESC');
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function daily(array $where, int $days = 30): array
    {
        $days = max(1, min($days, 366));
        $start = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $rows = $this->baseQuery($where)->where('pay_time', '>=', $start)
            ->field([
                Db::raw('DATE(pay_time) day'),
                Db::raw('count(order_id) order_count'),
                Db::raw('sum(pay_price) pay_amount'),
                Db::raw('sum(merchant_cost_amount) merchant_cost_amount'),
                Db::raw('sum(platform_profit) platform_profit'),
            ])->group('DATE(pay_time)')->order('day ASC')->select()->toArray();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['day']] = $row;
        }
        $list = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-' . $i . ' days'));
            $row = $indexed[$day] ?? [];
            $list[] = [
                'day' => $day,
                'order_count' => (int)($row['order_count'] ?? 0),
                'pay_amount' => $this->money($row['pay_amount'] ?? 0),
                'merchant_cost_amount' => $this->money($row['merchant_cost_amount'] ?? 0),
                'platform_profit' => $this->money($row['platform_profit'] ?? 0),
            ];
        }
        return $list;
    }

    public function monthCompare(array $where): array
    {
        $currentStart = date('Y-m-01 00:00:00');
        $currentEnd = date('Y-m-t 23:59:59');
        $previousStart = date('Y-m-01 00:00:00', strtotime('-1 month', strtotime($currentStart)));
        $previousEnd = date('Y-m-t 23:59:59', strtotime($previousStart));
        $current = $this->baseQuery($where)->whereBetween('pay_time', [$currentStart, $currentEnd])->sum('platform_profit');
        $previous = $this->baseQuery($where)->whereBetween('pay_time', [$previousStart, $previousEnd])->sum('platform_profit');
        return $this->calculator->monthCompare($current, $previous) + [
            'current_month' => date('Y-m'),
            'previous_month' => date('Y-m', strtotime($previousStart)),
        ];
    }

    public function overview(int $merId): array
    {
        $merchant = app()->make(MerchantRepository::class)->get($merId);
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        $mode = (int)($merchant->huimaidan_settlement_mode ?? MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL);
        $pool = $mode === MerchantRepository::HUIMAIDAN_SETTLEMENT_POOL ? $this->poolRepository->getOrCreateByMerId($merId) : null;
        $stats = $this->stats(['mer_id' => $merId]);
        $rules = $this->discountRuleDao->search([
            'mer_id' => $merId,
            'status' => 1,
            'active_at' => date('Y-m-d H:i:s'),
        ])->select()->toArray();
        return [
            'pool' => [
                'pool_id' => $pool ? (int)$pool->pool_id : 0,
                'balance' => $pool ? $this->money($pool->balance) : '0.00',
                'total_recharge' => $pool ? $this->money($pool->total_recharge) : '0.00',
                'total_consume' => $pool ? $this->money($pool->total_consume) : '0.00',
                'alarm_balance' => $pool ? $this->money($pool->alarm_balance) : '0.00',
                'alarm_enabled' => $pool ? (int)$pool->alarm_enabled : 0,
                'is_alarm' => $pool && (int)$pool->alarm_enabled === 1 && bccomp($pool->balance, $pool->alarm_balance, 2) <= 0,
            ],
            'settlement_mode' => $mode,
            'withdraw_rate' => $this->money($merchant->huimaidan_withdraw_rate ?? 0),
            'mer_money' => $this->money($merchant->mer_money ?? 0),
            'active_rule_count' => count($this->poolRulePolicy->usableRules($rules)),
            'settlement' => $stats,
        ];
    }

    public function merchantOrderList(int $merId, array $where, $page, $limit): array
    {
        $query = $this->merchantOrderQuery($merId, $where)->with($this->merchantOrderRelations())->order('order_id DESC');
        $count = $query->count();
        $list = $query->page($page, $limit)->select();
        return compact('count', 'list');
    }

    public function merchantOrderDetail(int $merId, int $orderId)
    {
        $order = StoreOrder::getDB()->where('mer_id', $merId)->where('order_id', $orderId)
            ->where('order_scene', OrderRepository::ORDER_SCENE)->where('is_del', 0)
            ->with($this->merchantOrderRelations(['groupOrder']))->find();
        if (!$order) {
            throw new ValidateException('订单不存在');
        }
        return $order;
    }

    public function merchantStatsData(int $merId, array $where): array
    {
        $where['mer_id'] = $merId;
        return $this->stats($where);
    }

    /**
     * 小时级收款趋势数据
     * 返回指定日期内每小时的收款金额和笔数
     *
     * @param int $merId 商户ID
     * @param string $date 日期关键字（today/yesterday）或日期格式 Y-m-d，默认 today
     * @return array
     */
    public function hourly(int $merId, string $date = ''): array
    {
        if (!$date) {
            $date = 'today';
        }

        $query = StoreOrder::getDB()
            ->where('order_scene', OrderRepository::ORDER_SCENE)
            ->where('paid', 1)
            ->where('is_del', 0)
            ->where('mer_id', $merId);

        // 使用 getModelTime 处理日期关键字（today/yesterday/month 等）或具体日期
        getModelTime($query, $date, 'pay_time');

        $rows = (clone $query)
            ->field([
                Db::raw('HOUR(pay_time) as hour'),
                Db::raw('COUNT(order_id) as order_count'),
                Db::raw('SUM(pay_price) as pay_amount'),
            ])
            ->group('HOUR(pay_time)')
            ->order('hour ASC')
            ->select()
            ->toArray();

        // 将结果按小时索引化
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['hour']] = [
                'order_count' => (int)$row['order_count'],
                'pay_amount' => $this->money($row['pay_amount']),
            ];
        }

        // 生成 0~23 全天24小时数据，缺失的小时补零
        $list = [];
        for ($h = 0; $h < 24; $h++) {
            $data = $indexed[$h] ?? ['order_count' => 0, 'pay_amount' => '0.00'];
            $list[] = [
                'hour' => $h,
                'hour_label' => sprintf('%02d:00', $h),
                'order_count' => $data['order_count'],
                'pay_amount' => $data['pay_amount'],
            ];
        }

        return [
            'date' => $date,
            'list' => $list,
        ];
    }

    protected function baseQuery(array $where)
    {
        return StoreOrder::getDB()->where('order_scene', OrderRepository::ORDER_SCENE)->where('paid', 1)->where('is_del', 0)
            ->when(isset($where['mer_id']) && $where['mer_id'] !== '', function ($query) use ($where) {
                $query->where('mer_id', (int)$where['mer_id']);
            })
            ->when(isset($where['order_sn']) && $where['order_sn'] !== '', function ($query) use ($where) {
                $query->whereLike('order_sn', "%{$where['order_sn']}%");
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'pay_time');
            })
            ->when(isset($where['settlement_mode']) && $where['settlement_mode'] !== '', function ($query) use ($where) {
                $query->where('settlement_mode', (int)$where['settlement_mode']);
            });
    }

    protected function withdrawFeeQuery(array $where)
    {
        return app()->make(FinancialDao::class)->search([
            'business_type' => 'huimaidan',
            'is_del' => 0,
            'mer_id' => $where['mer_id'] ?? '',
            'date' => $where['date'] ?? '',
        ])->where('Financial.status', 1)->where('Financial.financial_status', 1);
    }

    protected function merchantOrderQuery(int $merId, array $where)
    {
        return StoreOrder::getDB()->where('order_scene', OrderRepository::ORDER_SCENE)->where('is_del', 0)->where('mer_id', $merId)
            ->when(isset($where['paid']) && $where['paid'] !== '', function ($query) use ($where) {
                $query->where('paid', (int)$where['paid']);
            })
            ->when(isset($where['order_sn']) && $where['order_sn'] !== '', function ($query) use ($where) {
                $query->whereLike('order_sn', "%{$where['order_sn']}%");
            })
            ->when(isset($where['date']) && $where['date'] !== '', function ($query) use ($where) {
                getModelTime($query, $where['date'], 'pay_time');
            });
    }

    protected function merchantOrderRelations(array $relations = []): array
    {
        $relations['user'] = function ($query) {
            $query->field('uid,nickname,avatar');
        };
        return $relations;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
