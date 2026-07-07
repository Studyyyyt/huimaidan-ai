<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\OrderRepository;
use app\common\repositories\user\UserRelationRepository;

$orderReflection = new ReflectionClass(OrderRepository::class);
$order = $orderReflection->newInstanceWithoutConstructor();
$unsupported = $orderReflection->getMethod('unsupportedMiniProgramOrderFields');
$unsupported->setAccessible(true);

$fields = $unsupported->invoke($order, [
    'mer_id' => 5,
    'amount' => '100.00',
    'couponId' => 1,
    'usePoints' => false,
    'pointsAmount' => '3.00',
    'discountType' => 'discount',
]);
$expectedFields = ['pointsAmount', 'discountType'];
if ($fields !== $expectedFields) {
    throw new RuntimeException('支付型下单字段识别不正确: ' . var_export($fields, true));
}

$fields = $unsupported->invoke($order, [
    'mer_id' => 5,
    'amount' => '100.00',
    'pay_type' => 'balance',
]);
if ($fields !== []) {
    throw new RuntimeException('标准惠买单下单参数不应被拦截: ' . var_export($fields, true));
}

$relationReflection = new ReflectionClass(UserRelationRepository::class);
$relation = $relationReflection->newInstanceWithoutConstructor();
$payload = $relationReflection->getMethod('collectionCheckPayload');
$payload->setAccessible(true);

if ($payload->invoke($relation, true) !== ['isCollected' => true]) {
    throw new RuntimeException('已收藏状态响应不正确');
}
if ($payload->invoke($relation, false) !== ['isCollected' => false]) {
    throw new RuntimeException('未收藏状态响应不正确');
}

$storeGroupId = $relationReflection->getMethod('requestedStoreGroupId');
$storeGroupId->setAccessible(true);

if ($storeGroupId->invoke($relation, ['store_group_id' => '13']) !== 13) {
    throw new RuntimeException('收藏列表店铺分组筛选应规范为整数');
}

try {
    $storeGroupId->invoke($relation, ['store_group_id' => 'abc']);
    throw new RuntimeException('收藏列表店铺分组筛选格式错误时应抛出异常');
} catch (\ReflectionException $exception) {
    throw $exception;
} catch (\think\exception\ValidateException $exception) {
    if ($exception->getMessage() !== '店铺分组筛选格式错误') {
        throw new RuntimeException('收藏列表店铺分组筛选错误文案不正确: ' . $exception->getMessage());
    }
}

echo "HuimaidanMiniProgramControllerGuardTest passed\n";
