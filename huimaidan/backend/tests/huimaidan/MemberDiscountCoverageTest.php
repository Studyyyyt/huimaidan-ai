<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\MerchantDiscountRepository;

$reflection = new ReflectionClass(MerchantDiscountRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$method = $reflection->getMethod('missingMemberLevels');
$method->setAccessible(true);

$levels = [
    ['brokerage_level' => 1, 'brokerage_name' => '普通会员'],
    ['brokerage_level' => 2, 'brokerage_name' => 'VIP'],
    ['brokerage_level' => 3, 'brokerage_name' => '黑金会员'],
];
$discounts = [
    ['member_level' => 1, 'member_discount' => '0.90'],
    ['member_level' => 3, 'member_discount' => '0.70'],
];

$missing = $method->invoke($repository, $levels, $discounts);
if ($missing !== [2 => 'VIP']) {
    throw new RuntimeException('应识别未配置消费折扣的启用用户等级');
}

$discounts[] = ['member_level' => 2, 'member_discount' => '0.80'];
$missing = $method->invoke($repository, $levels, $discounts);
if ($missing !== []) {
    throw new RuntimeException('所有启用用户等级已配置时不应返回缺失项');
}

echo "MemberDiscountCoverageTest passed\n";
