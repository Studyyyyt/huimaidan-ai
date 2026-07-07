<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$statisticsPayload = $reflection->getMethod('orderStatisticsPayload');
$statisticsPayload->setAccessible(true);
$stats = $statisticsPayload->invoke($repository, 2, 3, 1);
if ($stats !== ['unpaid' => 2, 'completed' => 3, 'refund' => 1]) {
    throw new RuntimeException('小程序订单统计字段不正确: ' . var_export($stats, true));
}

$payResultPayload = $reflection->getMethod('payResultPayload');
$payResultPayload->setAccessible(true);
$paid = $payResultPayload->invoke($repository, [
    'order_id' => 11,
    'group_order_id' => 22,
    'paid' => 1,
    'pay_time' => '2026-06-01 12:30:00',
]);
if ($paid !== [
    'paid' => true,
    'orderId' => 22,
    'storeOrderId' => 11,
    'payTime' => '2026-06-01 12:30:00',
]) {
    throw new RuntimeException('已支付订单结果字段不正确: ' . var_export($paid, true));
}

$unpaid = $payResultPayload->invoke($repository, [
    'order_id' => 33,
    'group_order_id' => 44,
    'paid' => 0,
    'pay_time' => null,
]);
if ($unpaid !== [
    'paid' => false,
    'orderId' => 44,
    'storeOrderId' => 33,
    'payTime' => '',
]) {
    throw new RuntimeException('未支付订单结果字段不正确: ' . var_export($unpaid, true));
}

echo "OrderRepositoryMiniProgramSupportTest passed\n";
