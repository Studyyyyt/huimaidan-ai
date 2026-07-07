<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MemberDiscountCalculator;

$calculator = new MemberDiscountCalculator();

$result = $calculator->calculate('100.00', [
    'discount_id' => 7,
    'merchant_discount' => '0.60',
], [
    'member_discount_id' => 9,
    'member_level' => 2,
    'member_level_name' => 'VIP',
    'member_discount' => '0.80',
]);

if ($result['pay_amount'] !== '80.00'
    || $result['merchant_cost_amount'] !== '60.00'
    || $result['platform_profit'] !== '20.00'
    || $result['saved_amount'] !== '20.00') {
    throw new RuntimeException('会员商家差异折扣计算结果不正确');
}

if ($result['snapshot']['member_level'] !== 2
    || $result['snapshot']['member_level_name'] !== 'VIP'
    || $result['snapshot']['member_discount'] !== '0.80'
    || $result['snapshot']['merchant_discount'] !== '0.60') {
    throw new RuntimeException('会员商家差异折扣快照缺少关键字段');
}

$negativeProfit = $calculator->calculate('100.00', [
    'discount_id' => 8,
    'merchant_discount' => '0.60',
], [
    'member_discount_id' => 10,
    'member_level' => 3,
    'member_level_name' => '补贴会员',
    'member_discount' => '0.50',
]);

if ($negativeProfit['platform_profit'] !== '-10.00') {
    throw new RuntimeException('平台负差价必须按真实数据保留');
}

echo "MemberDiscountCalculatorTest passed\n";
