<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\DiscountEngineRepository;

$reflection = new ReflectionClass(DiscountEngineRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$result = $repository->publicResult([
    'mer_id' => 10,
    'rule_id' => 20,
    'original_amount' => '100.00',
    'pay_amount' => '80.00',
    'saved_amount' => '20.00',
    'pool_id' => 30,
    'merchant_cost_amount' => '90.00',
    'platform_profit' => '10.00',
    'pool_transaction_id' => 40,
]);

if ($result['mer_id'] !== 10 || $result['pay_amount'] !== '80.00') {
    throw new RuntimeException('公开优惠结果缺少用户端必要字段');
}
foreach (['pool_id', 'merchant_cost_amount', 'platform_profit', 'pool_transaction_id'] as $field) {
    if (array_key_exists($field, $result)) {
        throw new RuntimeException('公开优惠结果不应暴露内部字段: ' . $field);
    }
}

echo "DiscountEnginePublicResultTest passed\n";
