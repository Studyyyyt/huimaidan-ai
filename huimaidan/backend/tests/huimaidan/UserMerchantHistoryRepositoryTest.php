<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\UserMerchantHistoryRepository;
use think\exception\ValidateException;

$reflection = new ReflectionClass(UserMerchantHistoryRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$location = $reflection->getMethod('location');
$location->setAccessible(true);
$requestedStoreGroupId = $reflection->getMethod('requestedStoreGroupId');
$requestedStoreGroupId->setAccessible(true);
$historyPayload = $reflection->getMethod('historyPayload');
$historyPayload->setAccessible(true);
$batchDeletePayload = $reflection->getMethod('batchDeletePayload');
$batchDeletePayload->setAccessible(true);

$assertSame = function ($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': ' . var_export($actual, true));
    }
};
$assertThrows = function (callable $callback, string $expectedMessage, string $message): void {
    try {
        $callback();
    } catch (ValidateException $e) {
        if ($e->getMessage() !== $expectedMessage) {
            throw new RuntimeException($message . '，实际错误: ' . $e->getMessage());
        }
        return;
    }
    throw new RuntimeException($message);
};

$assertSame(null, $location->invoke($repository, []), '未提供定位时应返回 null');
$assertSame(
    ['lat' => 30.65, 'long' => 104.08],
    $location->invoke($repository, ['latitude' => '30.65', 'longitude' => '104.08']),
    '经纬度应规范为浮点坐标'
);
$assertThrows(function () use ($location, $repository) {
    $location->invoke($repository, ['latitude' => '30.65']);
}, '请同时提供经纬度', '经纬度缺一应失败');
$assertThrows(function () use ($location, $repository) {
    $location->invoke($repository, ['latitude' => 'abc', 'longitude' => '104.08']);
}, '经纬度格式错误', '经纬度格式错误应失败');

$assertSame(null, $requestedStoreGroupId->invoke($repository, []), '未提供店铺分组时应返回 null');
$assertSame(13, $requestedStoreGroupId->invoke($repository, ['store_group_id' => '13']), '店铺分组筛选应规范为整数');
$assertThrows(function () use ($requestedStoreGroupId, $repository) {
    $requestedStoreGroupId->invoke($repository, ['store_group_id' => 'abc']);
}, '店铺分组参数格式错误', '店铺分组格式错误应失败');

$payload = $historyPayload->invoke($repository, [
    'user_merchant_history_id' => 12,
    'mer_id' => 1001,
    'last_visit_time' => '2026-06-18 14:30:00',
    'visit_count' => 3,
], ['mer_id' => 1001, 'mer_name' => '惠买单火锅店']);
$assertSame([
    'history_id' => 12,
    'mer_id' => 1001,
    'browseTime' => '2026-06-18 14:30:00',
    'visitCount' => 3,
    'shop' => ['mer_id' => 1001, 'mer_name' => '惠买单火锅店'],
], $payload, '历史列表字段组装不正确');

$assertSame(
    ['clear' => false, 'history_ids' => [12, 13]],
    $batchDeletePayload->invoke($repository, ['history_ids' => ['12', 13]]),
    '批量删除 ID 应规范为整数数组'
);
$assertSame(
    ['clear' => true, 'history_ids' => []],
    $batchDeletePayload->invoke($repository, ['clear' => 1]),
    '清空请求应规范为 clear'
);
$assertThrows(function () use ($batchDeletePayload, $repository) {
    $batchDeletePayload->invoke($repository, []);
}, '请选择要删除的浏览记录', '未传删除参数应失败');
$assertThrows(function () use ($batchDeletePayload, $repository) {
    $batchDeletePayload->invoke($repository, ['clear' => 1, 'history_ids' => [12]]);
}, '清空和批量删除不能同时操作', 'clear 与 history_ids 同传应失败');

echo "UserMerchantHistoryRepositoryTest passed\n";
