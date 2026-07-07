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

class DiscountCalculator
{
    public const TYPE_DISCOUNT = 1;
    public const TYPE_COUPON = 2;
    public const TYPE_POINTS = 3;

    public function best($amount, array $rules): array
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new \InvalidArgumentException('消费金额必须大于0');
        }

        if (!$rules) {
            return $this->formatResult($amount, null, $amount, $amount, '0.00');
        }

        $best = null;
        foreach ($rules as $rule) {
            $result = $this->calculateRule($amount, $rule);
            if ($best === null || bccomp($result['pay_amount'], $best['pay_amount'], 2) < 0) {
                $best = $result;
            }
        }

        return $best;
    }

    public function calculateRule($amount, array $rule): array
    {
        $amount = $this->money($amount);
        $type = (int)($rule['rule_type'] ?? 0);

        switch ($type) {
            case self::TYPE_DISCOUNT:
                $payAmount = $this->money(bcmul($amount, (string)($rule['platform_discount'] ?? '1'), 4));
                break;
            case self::TYPE_COUPON:
                $payAmount = $this->money(bcsub($amount, (string)($rule['coupon_amount'] ?? '0'), 4));
                break;
            case self::TYPE_POINTS:
                $discount = bcmul($amount, (string)($rule['point_ratio'] ?? '0'), 4);
                $payAmount = $this->money(bcsub($amount, $discount, 4));
                break;
            default:
                throw new \InvalidArgumentException('优惠规则类型无效');
        }

        if (bccomp($payAmount, '0.01', 2) < 0) {
            $payAmount = '0.01';
        }

        $merchantRate = (string)($rule['merchant_cost'] ?? ($type === self::TYPE_DISCOUNT ? '1' : '1'));
        $merchantCostAmount = $this->money(bcmul($amount, $merchantRate, 4));
        $platformProfit = $this->money(bcsub($payAmount, $merchantCostAmount, 4));

        return $this->formatResult($amount, $rule, $payAmount, $merchantCostAmount, $platformProfit);
    }

    private function formatResult(string $amount, ?array $rule, string $payAmount, string $merchantCostAmount, string $platformProfit): array
    {
        $savedAmount = $this->money(bcsub($amount, $payAmount, 4));
        $ruleType = $rule ? (int)$rule['rule_type'] : null;

        return [
            'original_amount' => $amount,
            'rule_id' => $rule ? (int)$rule['rule_id'] : null,
            'pool_id' => $rule && isset($rule['pool_id']) ? ($rule['pool_id'] === null ? null : (int)$rule['pool_id']) : null,
            'discount_type' => $ruleType,
            'discount_type_label' => $this->typeLabel($ruleType),
            'discount_rule' => $rule['title'] ?? '暂无优惠',
            'discount_rate' => $rule['platform_discount'] ?? null,
            'pay_amount' => $payAmount,
            'saved_amount' => $savedAmount,
            'merchant_cost_amount' => $merchantCostAmount,
            'platform_profit' => $platformProfit,
            'snapshot' => [
                'rule_id' => $rule ? (int)$rule['rule_id'] : null,
                'rule_type' => $ruleType,
                'rule_type_label' => $this->typeLabel($ruleType),
                'title' => $rule['title'] ?? '暂无优惠',
                'platform_discount' => $rule['platform_discount'] ?? null,
                'merchant_cost' => $rule['merchant_cost'] ?? null,
                'coupon_amount' => $rule['coupon_amount'] ?? null,
                'point_ratio' => $rule['point_ratio'] ?? null,
                'original_amount' => $amount,
                'pay_amount' => $payAmount,
                'saved_amount' => $savedAmount,
                'merchant_cost_amount' => $merchantCostAmount,
                'platform_profit' => $platformProfit,
            ],
        ];
    }

    private function typeLabel(?int $type): string
    {
        switch ($type) {
            case self::TYPE_DISCOUNT:
                return '折扣';
            case self::TYPE_COUPON:
                return '优惠券';
            case self::TYPE_POINTS:
                return '积分';
            default:
                return '无优惠';
        }
    }

    private function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
