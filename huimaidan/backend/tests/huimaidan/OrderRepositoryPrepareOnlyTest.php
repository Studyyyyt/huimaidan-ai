<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$unsupported = $reflection->getMethod('unsupportedMiniProgramPrepareFields');
$unsupported->setAccessible(true);
$fields = $unsupported->invoke($repository, [
    'mer_id' => 1001,
    'amount' => '88.00',
    'product_id' => 1,
    'sku' => 'default',
    'cart_id' => 2,
    'cartIds' => [2],
    'productAttr' => ['规格'],
    'pay_type' => 'routine',
    'couponId' => 101,
    'usePoints' => true,
]);
$expectedFields = ['product_id', 'sku', 'cart_id', 'cartIds', 'productAttr', 'pay_type', 'couponId', 'usePoints'];
if ($fields !== $expectedFields) {
    throw new RuntimeException('待支付订单未支持字段识别不正确: ' . var_export($fields, true));
}

$fields = $unsupported->invoke($repository, [
    'mer_id' => 1001,
    'amount' => '88.00',
    'mark' => '用户备注',
]);
if ($fields !== []) {
    throw new RuntimeException('待支付订单标准参数不应被拦截: ' . var_export($fields, true));
}

$payload = $reflection->getMethod('preparePayload');
$payload->setAccessible(true);
$result = $payload->invoke($repository, [
    'group_order_id' => 123,
    'order_id' => 456,
    'order_sn' => '202606110001H',
    'pay_price' => '52.80',
    'discount' => [
        'rule_id' => 1,
        'rule_type' => 1,
        'rule_type_label' => '折扣',
        'title' => '惠买单6折',
        'original_amount' => '88.00',
        'pay_amount' => '52.80',
        'saved_amount' => '35.20',
    ],
]);

$expected = [
    'group_order_id' => 123,
    'order_id' => 456,
    'order_sn' => '202606110001H',
    'pay_price' => '52.80',
    'discount' => [
        'rule_id' => 1,
        'rule_type' => 1,
        'rule_type_label' => '折扣',
        'title' => '惠买单6折',
        'original_amount' => '88.00',
        'pay_amount' => '52.80',
        'saved_amount' => '35.20',
    ],
];
if ($result !== $expected) {
    throw new RuntimeException('待支付订单响应字段不正确: ' . var_export($result, true));
}

echo "OrderRepositoryPrepareOnlyTest passed\n";
