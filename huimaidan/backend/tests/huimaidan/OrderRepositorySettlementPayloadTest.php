<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$method = $reflection->getMethod('settlementFields');
$method->setAccessible(true);

$fields = $method->invoke($repository, ['huimaidan_settlement_mode' => 2]);
if ($fields !== ['settlement_mode' => 2, 'huimaidan_income_status' => 0]) {
    throw new RuntimeException('订单应快照商户提现模式');
}

$fields = $method->invoke($repository, []);
if ($fields !== ['settlement_mode' => 1, 'huimaidan_income_status' => 0]) {
    throw new RuntimeException('订单未配置模式时应默认垫资池模式');
}

$recordMethod = $reflection->getMethod('huimaidanIncomeRecord');
$recordMethod->setAccessible(true);
$order = (object)[
    'order_id' => 12,
    'order_sn' => 'HMD123',
    'uid' => 34,
    'real_name' => '张三',
    'mer_id' => 56,
    'pay_price' => '88.80',
    'merchant_cost_amount' => '75.00',
    'pay_type' => 2,
];
$record = $recordMethod->invoke($repository, $order);
if ($record['financial_type'] !== 'huimaidan_order_income' || $record['number'] !== '75.00' || $record['mer_id'] !== 56) {
    throw new RuntimeException('提现模式订单应按商家结算金额入账');
}

echo "OrderRepositorySettlementPayloadTest passed\n";
