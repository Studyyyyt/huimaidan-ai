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

class SettlementCalculator
{
    public function summary(array $orders): array
    {
        $payAmount = '0.00';
        $merchantCostAmount = '0.00';
        $platformProfit = '0.00';
        foreach ($orders as $order) {
            $payAmount = bcadd($payAmount, (string)($order['pay_price'] ?? '0.00'), 2);
            $merchantCostAmount = bcadd($merchantCostAmount, (string)($order['merchant_cost_amount'] ?? '0.00'), 2);
            $platformProfit = bcadd($platformProfit, (string)($order['platform_profit'] ?? '0.00'), 2);
        }
        return [
            'order_count' => count($orders),
            'pay_amount' => $this->money($payAmount),
            'merchant_cost_amount' => $this->money($merchantCostAmount),
            'platform_profit' => $this->money($platformProfit),
        ];
    }

    public function monthCompare($currentAmount, $previousAmount): array
    {
        $currentAmount = $this->money($currentAmount);
        $previousAmount = $this->money($previousAmount);
        $changeAmount = bcsub($currentAmount, $previousAmount, 2);
        return [
            'current_amount' => $currentAmount,
            'previous_amount' => $previousAmount,
            'change_amount' => $this->money($changeAmount),
            'change_rate' => bccomp($previousAmount, '0.00', 2) === 0
                ? null
                : $this->money(bcmul(bcdiv($changeAmount, $previousAmount, 4), '100', 2)),
        ];
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
