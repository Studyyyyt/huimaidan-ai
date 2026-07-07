<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MerchantDiscoveryRepository;

$reflection = new ReflectionClass(MerchantDiscoveryRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$filtersPayload = $reflection->getMethod('filtersPayload');
$filtersPayload->setAccessible(true);

$filters = $filtersPayload->invoke($repository, [
    ['merchant_category_id' => 3, 'category_name' => '美食餐饮'],
], [
    ['id' => 1101, 'name' => '测试市', 'code' => '110100'],
]);

$byKey = [];
foreach ($filters as $filter) {
    $byKey[$filter['key']] = $filter;
}

foreach (['distance', 'category', 'city', 'sort'] as $key) {
    if (!isset($byKey[$key])) {
        throw new RuntimeException('筛选配置缺少分组: ' . $key);
    }
}

if ($byKey['category']['options'][0] !== ['id' => 3, 'name' => '美食餐饮', 'value' => 3]) {
    throw new RuntimeException('分类筛选应来自真实商户分类: ' . var_export($byKey['category']['options'], true));
}

if ($byKey['city']['options'][0] !== ['id' => 1101, 'name' => '测试市', 'value' => 1101, 'code' => '110100']) {
    throw new RuntimeException('城市筛选应来自真实城市: ' . var_export($byKey['city']['options'], true));
}

$sortValues = array_column($byKey['sort']['options'], 'value');
if ($sortValues !== ['default', 'location']) {
    throw new RuntimeException('排序筛选只能暴露后端已支持项: ' . var_export($sortValues, true));
}

$distanceValues = array_column($byKey['distance']['options'], 'value');
if ($distanceValues !== [1, 3, 5, 10]) {
    throw new RuntimeException('距离筛选选项不正确: ' . var_export($distanceValues, true));
}

$distanceLimit = $reflection->getMethod('distanceLimit');
$distanceLimit->setAccessible(true);
if ($distanceLimit->invoke($repository, ['distance' => '3']) !== '3.00') {
    throw new RuntimeException('距离筛选应格式化为两位小数');
}
if ($distanceLimit->invoke($repository, []) !== null) {
    throw new RuntimeException('未传距离时不应生成距离过滤条件');
}

$distanceSql = $reflection->getMethod('distanceSql');
$distanceSql->setAccessible(true);
$sql = $distanceSql->invoke($repository, 30.0, 120.0);
if (strpos($sql, 'POW(SIN(PI() * (`lat` - 30) / 360), 2)') === false) {
    throw new RuntimeException('距离 SQL 必须用纬度差作为 Haversine 第一项: ' . $sql);
}
if (strpos($sql, 'POW(SIN(PI() * (`long` - 120) / 360), 2)') === false) {
    throw new RuntimeException('距离 SQL 必须用经度差作为 Haversine 第二项: ' . $sql);
}
if (strpos($sql, 'POW(SIN(PI() * (120 - `long`) / 360), 2) + COS') !== false) {
    throw new RuntimeException('距离 SQL 不得把经度差和纬度差权重写反: ' . $sql);
}

$distanceKm = $reflection->getMethod('distanceKm');
$distanceKm->setAccessible(true);
$nearDistance = $distanceKm->invoke($repository, 30.0, 120.0, 30.001, 120.0);
if ($nearDistance <= 0.10 || $nearDistance >= 0.12) {
    throw new RuntimeException('商户列表展示距离不能先粗略四舍五入到 0.1km: ' . var_export($nearDistance, true));
}

$branchStorePayload = $reflection->getMethod('branchStorePayload');
$branchStorePayload->setAccessible(true);
$branches = $branchStorePayload->invoke($repository, [
    [
        'mer_id' => 55,
        'mer_name' => '惠买单',
        'branch_name' => '万达店',
        'mer_address' => '江汉路 1 号',
        'service_phone' => '13800138000',
        'mer_phone' => '13900139000',
        'long' => '114.30',
        'lat' => '30.60',
        'status' => 1,
        'is_del' => 0,
        'password' => 'secret',
    ],
    [
        'mer_id' => 56,
        'mer_name' => '王府井店',
        'branch_name' => '',
        'mer_address' => '中山路 2 号',
        'service_phone' => '',
        'mer_phone' => '13900139000',
        'long' => '114.31',
        'lat' => '30.61',
    ],
]);
$expectedBranches = [[
    'id' => 55,
    'mer_id' => 55,
    'name' => '万达店',
    'mer_name' => '惠买单',
    'branch_name' => '万达店',
    'store_branch_name' => '万达店',
    'address' => '江汉路 1 号',
    'mer_address' => '江汉路 1 号',
    'phone' => '13800138000',
    'longitude' => '114.30',
    'latitude' => '30.60',
], [
    'id' => 56,
    'mer_id' => 56,
    'name' => '王府井店',
    'mer_name' => '王府井店',
    'branch_name' => '',
    'store_branch_name' => '',
    'address' => '中山路 2 号',
    'mer_address' => '中山路 2 号',
    'phone' => '13900139000',
    'longitude' => '114.31',
    'latitude' => '30.61',
]];
if ($branches !== $expectedBranches) {
    throw new RuntimeException('门店列表应返回同一商户下的商户门店公开字段: ' . var_export($branches, true));
}

echo "MerchantDiscoveryFiltersTest passed\n";
