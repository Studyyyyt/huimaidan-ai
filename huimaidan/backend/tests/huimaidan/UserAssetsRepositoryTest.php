<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\UserBenefitRepository;

$reflection = new ReflectionClass(UserBenefitRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$payload = $reflection->getMethod('assetsPayload');
$payload->setAccessible(true);

$result = $payload->invoke($repository, [
    'brokerage_price' => '12.50',
    'integral' => 168,
    'total_coupon' => 3,
    'member_level' => 2,
]);

$expected = [
    'commission' => '12.50',
    'points' => 168,
    'couponCount' => 3,
    'vipLevel' => 2,
];
if ($result !== $expected) {
    throw new RuntimeException('用户资产聚合字段不正确: ' . var_export($result, true));
}

$empty = $payload->invoke($repository, []);
if ($empty !== [
    'commission' => '0.00',
    'points' => 0,
    'couponCount' => 0,
    'vipLevel' => 0,
]) {
    throw new RuntimeException('用户资产默认字段不正确: ' . var_export($empty, true));
}

$responsePayload = $reflection->getMethod('miniProgramSuccessPayload');
$responsePayload->setAccessible(true);
$response = $responsePayload->invoke($repository, $result);
$expectedResponse = [
    'code' => 0,
    'msg' => 'success',
    'data' => $expected,
];
if ($response !== $expectedResponse) {
    throw new RuntimeException('小程序资产接口响应格式不正确: ' . var_export($response, true));
}

echo "UserAssetsRepositoryTest passed\n";
