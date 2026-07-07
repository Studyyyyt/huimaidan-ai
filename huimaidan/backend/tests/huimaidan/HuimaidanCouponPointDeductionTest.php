<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;
use think\exception\ValidateException;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$discountReflection = new ReflectionClass(\app\common\repositories\huimaidan\MerchantDiscountRepository::class);
if (!$discountReflection->hasMethod('normalizeDiscountStackEnabled')) {
    throw new RuntimeException('商家折扣配置仓库应提供优惠叠加开关归一化方法');
}
$normalizeStack = $discountReflection->getMethod('normalizeDiscountStackEnabled');
$normalizeStack->setAccessible(true);
$discountRepository = $discountReflection->newInstanceWithoutConstructor();
foreach ([
    [1, 1],
    ['1', 1],
    [true, 1],
    ['true', 1],
    [0, 0],
    ['0', 0],
    [false, 0],
    [null, 1],
    ['', 1],
] as [$input, $expected]) {
    if ($normalizeStack->invoke($discountRepository, $input) !== $expected) {
        throw new RuntimeException('优惠叠加开关归一化不正确: ' . var_export($input, true));
    }
}

$orderUnsupported = $reflection->getMethod('unsupportedMiniProgramOrderFields');
$orderUnsupported->setAccessible(true);
$fields = $orderUnsupported->invoke($repository, [
    'mer_id' => 5,
    'amount' => '100.00',
    'couponId' => 101,
    'usePoints' => true,
    'pointsAmount' => '3.00',
    'discountType' => 'discount',
]);
if ($fields !== ['pointsAmount', 'discountType']) {
    throw new RuntimeException('支付型下单应允许 couponId/usePoints，仅拦截未定义抵扣字段: ' . var_export($fields, true));
}

$prepareUnsupported = $reflection->getMethod('unsupportedMiniProgramPrepareFields');
$prepareUnsupported->setAccessible(true);
$fields = $prepareUnsupported->invoke($repository, [
    'mer_id' => 5,
    'amount' => '100.00',
    'couponId' => 101,
    'usePoints' => true,
]);
if ($fields !== ['couponId', 'usePoints']) {
    throw new RuntimeException('非支付待支付订单应拒绝真实抵扣字段: ' . var_export($fields, true));
}

$normalizeConfig = $reflection->getMethod('normalizeDeductionConfig');
$normalizeConfig->setAccessible(true);
$config = $normalizeConfig->invoke($repository, ['integral_status' => 1, 'integral_money' => '0.01']);
if ($config !== [
    'stack_enabled' => true,
    'integral_enabled' => true,
    'integral_money' => '0.01',
]) {
    throw new RuntimeException('缺少惠买单叠加配置时应使用默认允许叠加策略: ' . var_export($config, true));
}

$config = $normalizeConfig->invoke($repository, [
    'huimaidan_discount_stack_enabled' => '1',
    'integral_status' => '1',
    'integral_money' => '0.01',
]);
if ($config !== [
    'stack_enabled' => true,
    'integral_enabled' => true,
    'integral_money' => '0.01',
]) {
    throw new RuntimeException('惠买单抵扣配置归一化不正确: ' . var_export($config, true));
}

$pointDeduction = $reflection->getMethod('pointDeduction');
$pointDeduction->setAccessible(true);
$points = $pointDeduction->invoke($repository, 600, '0.01', '5.00');
if ($points !== ['integral' => 499, 'integral_price' => '4.99']) {
    throw new RuntimeException('积分最大抵扣应保留最低 0.01 元实付: ' . var_export($points, true));
}

$apply = $reflection->getMethod('applyDeductionAmounts');
$apply->setAccessible(true);
$result = $apply->invoke($repository, [
    'pay_amount' => '80.00',
    'saved_amount' => '20.00',
    'merchant_cost_amount' => '70.00',
    'platform_profit' => '10.00',
    'snapshot' => [
        'pay_amount' => '80.00',
        'saved_amount' => '20.00',
        'merchant_cost_amount' => '70.00',
        'platform_profit' => '10.00',
    ],
], [
    'coupon_user_id' => 101,
    'coupon_price' => '10.00',
], [
    'integral' => 500,
    'integral_price' => '5.00',
], $config);

if ($result['pay_amount'] !== '65.00' || $result['saved_amount'] !== '35.00') {
    throw new RuntimeException('叠加抵扣后实付和优惠总额不正确: ' . var_export($result, true));
}
foreach ([
    'coupon_user_id' => 101,
    'coupon_deduction_amount' => '10.00',
    'integral' => 500,
    'integral_deduction_amount' => '5.00',
    'platform_bear_coupon_amount' => '10.00',
    'platform_bear_integral_amount' => '5.00',
] as $field => $expected) {
    if (($result['snapshot'][$field] ?? null) !== $expected) {
        throw new RuntimeException('抵扣快照字段不正确: ' . $field . ' => ' . var_export($result['snapshot'], true));
    }
}

$noStackConfig = [
    'stack_enabled' => false,
    'integral_enabled' => true,
    'integral_money' => '0.01',
];
try {
    $apply->invoke($repository, [
        'pay_amount' => '80.00',
        'saved_amount' => '20.00',
        'merchant_cost_amount' => '70.00',
        'platform_profit' => '10.00',
        'snapshot' => ['saved_amount' => '20.00'],
    ], [
        'coupon_user_id' => 101,
        'coupon_price' => '10.00',
    ], [
        'integral' => 0,
        'integral_price' => '0.00',
    ], $noStackConfig);
    throw new RuntimeException('配置为不叠加时不应允许折扣和券同时使用');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '当前配置不支持优惠叠加') {
        throw $e;
    }
}

echo "HuimaidanCouponPointDeductionTest passed\n";
