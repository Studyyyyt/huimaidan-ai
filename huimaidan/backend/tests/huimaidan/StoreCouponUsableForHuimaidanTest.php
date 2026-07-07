<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\store\coupon\StoreCouponUserRepository;

$reflection = new ReflectionClass(StoreCouponUserRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();

$isUsable = $reflection->getMethod('isHuimaidanCouponUsable');
$isUsable->setAccessible(true);
$publicCoupon = $reflection->getMethod('publicHuimaidanCoupon');
$publicCoupon->setAccessible(true);

$base = [
    'coupon_user_id' => 101,
    'coupon_id' => 201,
    'coupon_title' => '满100减10',
    'coupon_price' => '10.00',
    'use_min_price' => '100.00',
    'end_time' => '2026-07-01 00:00:00',
    'status' => 0,
    'mer_id' => 5,
    'coupon' => [
        'type' => 0,
        'status' => 1,
        'is_del' => 0,
    ],
    'product' => [],
];

if (!$isUsable->invoke($repository, $base, 5, '100.00')) {
    throw new RuntimeException('当前商户店铺券应可用于惠买单');
}

if ($isUsable->invoke($repository, $base, 5, '99.99')) {
    throw new RuntimeException('未达到门槛的券不应可用');
}

$platform = $base;
$platform['mer_id'] = 0;
$platform['coupon']['type'] = 10;
if (!$isUsable->invoke($repository, $platform, 5, '100.00')) {
    throw new RuntimeException('平台通用券应可用于惠买单');
}

$crossStore = $base;
$crossStore['mer_id'] = 0;
$crossStore['coupon']['type'] = 12;
$crossStore['product'] = [['product_id' => 5], ['product_id' => 9]];
if (!$isUsable->invoke($repository, $crossStore, 5, '100.00')) {
    throw new RuntimeException('包含当前商户的平台跨店券应可用');
}

$crossStore['product'] = [['product_id' => 9]];
if ($isUsable->invoke($repository, $crossStore, 5, '100.00')) {
    throw new RuntimeException('不包含当前商户的平台跨店券不应可用');
}

$productCoupon = $base;
$productCoupon['coupon']['type'] = 1;
if ($isUsable->invoke($repository, $productCoupon, 5, '100.00')) {
    throw new RuntimeException('无商品上下文时商品券不应作为惠买单可用券返回');
}

$payload = $publicCoupon->invoke($repository, $base);
$expected = [
    'id' => 101,
    'couponId' => 201,
    'name' => '满100减10',
    'amount' => '10.00',
    'threshold' => '100.00',
    'condition' => '满100.00元可用',
    'expireTime' => '2026-07-01 00:00:00',
    'usedTime' => '',
    'status' => 'unused',
];
if ($payload !== $expected) {
    throw new RuntimeException('小程序优惠券展示字段不正确: ' . var_export($payload, true));
}

echo "StoreCouponUsableForHuimaidanTest passed\n";
