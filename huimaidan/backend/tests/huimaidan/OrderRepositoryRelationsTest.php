<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;

$reflection = new ReflectionClass(OrderRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$userOrderRelations = $reflection->getMethod('userOrderRelations');
$userOrderRelations->setAccessible(true);
$relations = $userOrderRelations->invoke($repository, ['groupOrder']);

if (!isset($relations['groupOrder']) || !is_callable($relations['groupOrder'])
    || !isset($relations['merchant']) || !is_callable($relations['merchant'])) {
    throw new RuntimeException('用户订单关联应保留 groupOrder 并配置商户白名单');
}

$query = new class {
    public $fields;

    public function field(string $fields): void
    {
        $this->fields = $fields;
    }
};
$relations['merchant']($query);
if ($query->fields !== 'mer_id,mer_name,mer_avatar,mer_address') {
    throw new RuntimeException('用户订单商户白名单不正确: ' . $query->fields);
}

$relations['groupOrder']($query);
if ($query->fields !== 'group_order_id,group_order_sn,paid,pay_type,pay_price,create_time,pay_time') {
    throw new RuntimeException('用户订单主单白名单不正确: ' . $query->fields);
}

$publicDiscountSnapshot = $reflection->getMethod('publicDiscountSnapshot');
$publicDiscountSnapshot->setAccessible(true);
$discount = $publicDiscountSnapshot->invoke($repository, [
    'title' => '测试优惠',
    'pay_amount' => '80.00',
    'saved_amount' => '20.00',
    'pool_id' => 10,
    'merchant_cost_amount' => '90.00',
    'platform_profit' => '10.00',
]);
if ($discount['title'] !== '测试优惠' || $discount['pay_amount'] !== '80.00') {
    throw new RuntimeException('公开订单优惠快照缺少用户端必要字段');
}
foreach (['pool_id', 'merchant_cost_amount', 'platform_profit'] as $field) {
    if (array_key_exists($field, $discount)) {
        throw new RuntimeException('公开订单优惠快照不应暴露内部字段: ' . $field);
    }
}

echo "OrderRepositoryRelationsTest passed\n";
