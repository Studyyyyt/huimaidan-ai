<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\DiscountCalculator;

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$calculator = new DiscountCalculator();

$result = $calculator->best('300.00', [
    [
        'rule_id' => 1,
        'rule_type' => DiscountCalculator::TYPE_DISCOUNT,
        'title' => '平台6折',
        'platform_discount' => '0.60',
        'merchant_cost' => '0.50',
        'coupon_amount' => '0.00',
        'point_ratio' => '0.00',
        'pool_id' => 9,
    ],
    [
        'rule_id' => 2,
        'rule_type' => DiscountCalculator::TYPE_COUPON,
        'title' => '立减80',
        'platform_discount' => '0.00',
        'merchant_cost' => '1.00',
        'coupon_amount' => '80.00',
        'point_ratio' => '0.00',
        'pool_id' => null,
    ],
    [
        'rule_id' => 3,
        'rule_type' => DiscountCalculator::TYPE_POINTS,
        'title' => '积分抵20%',
        'platform_discount' => '0.00',
        'merchant_cost' => '1.00',
        'coupon_amount' => '0.00',
        'point_ratio' => '0.20',
        'pool_id' => null,
    ],
]);

assertSameValue('180.00', $result['pay_amount'], '折扣规则应在三种优惠中取最低实付');
assertSameValue('150.00', $result['merchant_cost_amount'], '折扣规则应按商家底价折扣计算垫资池扣减金额');
assertSameValue('30.00', $result['platform_profit'], '平台差价应等于用户实付减商家底价');
assertSameValue(1, $result['rule_id'], '应返回命中的规则ID');
assertSameValue(9, $result['pool_id'], '应返回命中规则关联的垫资池ID');

$floorResult = $calculator->best('10.00', [
    [
        'rule_id' => 4,
        'rule_type' => DiscountCalculator::TYPE_COUPON,
        'title' => '立减20',
        'platform_discount' => '0.00',
        'merchant_cost' => '1.00',
        'coupon_amount' => '20.00',
        'point_ratio' => '0.00',
        'pool_id' => null,
    ],
]);

assertSameValue('0.01', $floorResult['pay_amount'], '优惠后实付金额不能低于0.01');
assertSameValue('10.00', $floorResult['merchant_cost_amount'], '非折扣规则默认按原价作为商家底价');
assertSameValue('-9.99', $floorResult['platform_profit'], '优惠金额超过平台折扣价时应显式暴露负差价');

$emptyResult = $calculator->best('88.88', []);
assertSameValue('88.88', $emptyResult['pay_amount'], '无优惠规则时应按原价支付');
assertSameValue(null, $emptyResult['rule_id'], '无优惠规则时规则ID为空');

echo "DiscountCalculatorTest passed\n";
