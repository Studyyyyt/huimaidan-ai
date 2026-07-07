<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MemberDiscountCalculator;
use app\common\repositories\huimaidan\OrderRepository;

$calculator = new MemberDiscountCalculator();
$result = $calculator->calculateWithoutMemberDiscount('100.00', [
    'discount_id' => 7,
    'merchant_discount' => '0.60',
    'pool_id' => 3,
]);

if ($result['pay_amount'] !== '100.00'
    || $result['saved_amount'] !== '0.00'
    || $result['merchant_cost_amount'] !== '60.00'
    || $result['platform_profit'] !== '40.00') {
    throw new RuntimeException('关闭会员折扣时应保留原价实付和商户结算折扣: ' . var_export($result, true));
}

if (($result['snapshot']['member_discount_enabled'] ?? null) !== false
    || ($result['snapshot']['title'] ?? '') !== '不参与会员折扣') {
    throw new RuntimeException('关闭会员折扣快照缺少关键字段: ' . var_export($result['snapshot'] ?? [], true));
}
if (($result['member_discount_enabled'] ?? null) !== false) {
    throw new RuntimeException('公开结果需要标记是否参与会员折扣: ' . var_export($result, true));
}

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$orderUnsupported = $reflection->getMethod('unsupportedMiniProgramOrderFields');
$orderUnsupported->setAccessible(true);
$fields = $orderUnsupported->invoke($repository, [
    'mer_id' => 5,
    'amount' => '100.00',
    'pay_type' => 'routine',
    'useMemberDiscount' => false,
    'couponId' => 101,
    'usePoints' => true,
]);
if ($fields !== []) {
    throw new RuntimeException('支付型下单应允许关闭会员折扣并继续使用券和积分: ' . var_export($fields, true));
}

$prepareUnsupported = $reflection->getMethod('unsupportedMiniProgramPrepareFields');
$prepareUnsupported->setAccessible(true);
$fields = $prepareUnsupported->invoke($repository, [
    'mer_id' => 5,
    'amount' => '100.00',
    'useMemberDiscount' => false,
]);
if ($fields !== []) {
    throw new RuntimeException('prepare 应允许 useMemberDiscount 用于试算展示: ' . var_export($fields, true));
}

$deductionInputs = $reflection->getMethod('discountInputs');
$deductionInputs->setAccessible(true);
$inputs = $deductionInputs->invoke($repository, ['useMemberDiscount' => false]);
if ($inputs !== ['use_member_discount' => false]) {
    throw new RuntimeException('会员折扣开关入参解析不正确: ' . var_export($inputs, true));
}
$inputs = $deductionInputs->invoke($repository, ['useMemberDiscount' => '', 'use_member_discount' => '']);
if ($inputs !== ['use_member_discount' => true]) {
    throw new RuntimeException('会员折扣开关未传时应默认参与会员折扣: ' . var_export($inputs, true));
}

echo "HuimaidanMemberDiscountSwitchTest passed\n";
