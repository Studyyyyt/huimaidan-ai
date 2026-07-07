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

class MemberDiscountCalculator
{
    public function calculate($amount, array $merchantDiscount, array $memberDiscount): array
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new \InvalidArgumentException('消费金额必须大于0');
        }

        $memberRate = $this->rate($memberDiscount['member_discount'] ?? null, '会员消费折扣无效');
        $merchantRate = $this->rate($merchantDiscount['merchant_discount'] ?? null, '商家结算折扣无效');
        $payAmount = $this->money(bcmul($amount, $memberRate, 4));
        if (bccomp($payAmount, '0.01', 2) < 0) {
            $payAmount = '0.01';
        }
        $merchantCostAmount = $this->money(bcmul($amount, $merchantRate, 4));
        $platformProfit = $this->money(bcsub($payAmount, $merchantCostAmount, 4));
        $savedAmount = $this->money(bcsub($amount, $payAmount, 4));

        return [
            'original_amount' => $amount,
            'discount_config_id' => (int)($merchantDiscount['discount_id'] ?? 0),
            'member_discount_id' => (int)($memberDiscount['member_discount_id'] ?? 0),
            'member_level' => (int)($memberDiscount['member_level'] ?? 0),
            'member_level_name' => (string)($memberDiscount['member_level_name'] ?? ''),
            'member_discount_enabled' => true,
            'discount_type' => DiscountCalculator::TYPE_DISCOUNT,
            'discount_type_label' => '会员折扣',
            'discount_rule' => '会员专享折扣',
            'discount_rate' => $memberRate,
            'merchant_discount' => $merchantRate,
            'pay_amount' => $payAmount,
            'saved_amount' => $savedAmount,
            'merchant_cost_amount' => $merchantCostAmount,
            'platform_profit' => $platformProfit,
            'pool_id' => isset($merchantDiscount['pool_id']) ? (int)$merchantDiscount['pool_id'] : null,
            'snapshot' => [
                'discount_config_id' => (int)($merchantDiscount['discount_id'] ?? 0),
                'member_discount_id' => (int)($memberDiscount['member_discount_id'] ?? 0),
                'member_level' => (int)($memberDiscount['member_level'] ?? 0),
                'member_level_name' => (string)($memberDiscount['member_level_name'] ?? ''),
                'member_discount' => $memberRate,
                'member_discount_enabled' => true,
                'merchant_discount' => $merchantRate,
                'rule_type' => DiscountCalculator::TYPE_DISCOUNT,
                'rule_type_label' => '会员折扣',
                'title' => '会员专享折扣',
                'original_amount' => $amount,
                'pay_amount' => $payAmount,
                'saved_amount' => $savedAmount,
                'merchant_cost_amount' => $merchantCostAmount,
                'platform_profit' => $platformProfit,
            ],
        ];
    }

    public function calculateWithoutMemberDiscount($amount, array $merchantDiscount): array
    {
        $amount = $this->money($amount);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new \InvalidArgumentException('消费金额必须大于0');
        }

        $merchantRate = $this->rate($merchantDiscount['merchant_discount'] ?? null, '商家结算折扣无效');
        $merchantCostAmount = $this->money(bcmul($amount, $merchantRate, 4));
        $platformProfit = $this->money(bcsub($amount, $merchantCostAmount, 4));

        return [
            'original_amount' => $amount,
            'discount_config_id' => (int)($merchantDiscount['discount_id'] ?? 0),
            'member_discount_id' => 0,
            'member_level' => 0,
            'member_level_name' => '',
            'member_discount_enabled' => false,
            'discount_type' => DiscountCalculator::TYPE_DISCOUNT,
            'discount_type_label' => '不参与会员折扣',
            'discount_rule' => '不参与会员折扣',
            'discount_rate' => '1.00',
            'merchant_discount' => $merchantRate,
            'pay_amount' => $amount,
            'saved_amount' => '0.00',
            'merchant_cost_amount' => $merchantCostAmount,
            'platform_profit' => $platformProfit,
            'pool_id' => isset($merchantDiscount['pool_id']) ? (int)$merchantDiscount['pool_id'] : null,
            'snapshot' => [
                'discount_config_id' => (int)($merchantDiscount['discount_id'] ?? 0),
                'member_discount_id' => 0,
                'member_level' => 0,
                'member_level_name' => '',
                'member_discount' => '1.00',
                'member_discount_enabled' => false,
                'merchant_discount' => $merchantRate,
                'rule_type' => DiscountCalculator::TYPE_DISCOUNT,
                'rule_type_label' => '不参与会员折扣',
                'title' => '不参与会员折扣',
                'original_amount' => $amount,
                'pay_amount' => $amount,
                'saved_amount' => '0.00',
                'merchant_cost_amount' => $merchantCostAmount,
                'platform_profit' => $platformProfit,
            ],
        ];
    }

    protected function rate($rate, string $message): string
    {
        $rate = $this->money($rate);
        if (bccomp($rate, '0.00', 2) <= 0 || bccomp($rate, '1.00', 2) > 0) {
            throw new \InvalidArgumentException($message);
        }
        return $rate;
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}
