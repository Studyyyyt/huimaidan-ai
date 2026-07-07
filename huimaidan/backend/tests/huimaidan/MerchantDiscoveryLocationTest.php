<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MerchantDiscoveryRepository;
use think\exception\ValidateException;

$reflection = new ReflectionClass(MerchantDiscoveryRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$location = $reflection->getMethod('location');
$location->setAccessible(true);
$publicMerchant = $reflection->getMethod('publicMerchant');
$publicMerchant->setAccessible(true);
$publicRules = $reflection->getMethod('publicRules');
$publicRules->setAccessible(true);

$assertSame = function ($expected, $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': ' . var_export($actual, true));
    }
};
$assertThrows = function (array $where, string $message) use ($location, $repository): void {
    try {
        $location->invoke($repository, $where);
    } catch (ValidateException $e) {
        return;
    }
    throw new RuntimeException($message);
};

$assertSame(null, $location->invoke($repository, []), '未提供定位时应返回 null');
$assertSame(
    ['lat' => 0.0, 'long' => 0.0],
    $location->invoke($repository, ['latitude' => '0', 'longitude' => '0']),
    '零坐标应作为有效定位保留'
);
$assertThrows(['latitude' => '30.1'], '只传纬度时应失败');
$assertThrows(['latitude' => 'abc', 'longitude' => '120.1'], '非数字定位应失败');
$assertThrows(['latitude' => '91', 'longitude' => '120.1'], '越界定位应失败');
$publicResult = $publicMerchant->invoke($repository, [
    'mer_id' => 1,
    'mer_name' => '测试商户',
    'service_phone' => '15780282354',
    'product_score' => '5.0',
    'service_score' => '4.8',
    'postage_score' => '4.6',
    'sales' => 50,
    'create_time' => 1718071200,
    'real_name' => '后台联系人',
    'mer_phone' => '13800000000',
    'bank_number' => '6222000000000000',
]);
$assertSame(1, $publicResult['mer_id'], 'mer_id 应保留');
$assertSame('测试商户', $publicResult['mer_name'], 'mer_name 应保留');
$assertSame('15780282354', $publicResult['service_phone'], 'service_phone 应保留');
$assertSame('13800000000', $publicResult['mer_phone'], 'mer_phone 应保留用于联系电话展示');
$assertSame(50, $publicResult['sales'], 'sales 应保留');
$assertSame(false, array_key_exists('real_name', $publicResult), 'real_name 应被过滤');
$assertSame(false, array_key_exists('bank_number', $publicResult), 'bank_number 应被过滤');
$assertSame(
    [['rule_id' => 1, 'rule_type' => 1, 'platform_discount' => '0.60']],
    $publicRules->invoke($repository, [[
        'rule_id' => 1,
        'rule_type' => 1,
        'platform_discount' => '0.60',
        'merchant_cost' => '0.50',
        'pool_id' => 2,
        'pool' => ['balance' => '100.00'],
    ]]),
    '公共详情应过滤商家底价和垫资池信息'
);

echo "MerchantDiscoveryLocationTest passed\n";
