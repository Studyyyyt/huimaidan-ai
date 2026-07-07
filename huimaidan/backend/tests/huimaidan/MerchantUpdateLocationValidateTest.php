<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\validate\merchant\MerchantUpdateValidate;
use think\exception\ValidateException;

$base = [
    'mer_info' => '测试店铺简介',
    'mer_avatar' => '/avatar.jpg',
    'mer_banner' => '/banner.jpg',
    'mini_banner' => '/mini.jpg',
    'mer_keyword' => '测试',
    'mer_address' => '四川省成都市高新区测试路 1 号',
    'services_type' => 0,
];

$assertValid = function (array $payload, string $message) use ($base): void {
    $validator = new MerchantUpdateValidate();
    try {
        $validator->check(array_merge($base, $payload));
    } catch (ValidateException $e) {
        throw new RuntimeException($message . ': ' . $e->getMessage());
    }
};

$assertInvalid = function (array $payload, string $contains, string $message) use ($base): void {
    $validator = new MerchantUpdateValidate();
    try {
        $validator->check(array_merge($base, $payload));
    } catch (ValidateException $e) {
        if (strpos($e->getMessage(), $contains) === false) {
            throw new RuntimeException($message . ': unexpected message ' . $e->getMessage());
        }
        return;
    }
    throw new RuntimeException($message);
};

$assertValid(['long' => '', 'lat' => ''], '经纬度均为空时应允许保存');
$assertValid(['long' => '104.080000', 'lat' => '30.650000'], '合法 gcj02 坐标应允许保存');
$assertInvalid(['long' => '104.080000', 'lat' => ''], '请同时填写经纬度', '只填经度应失败');
$assertInvalid(['long' => '', 'lat' => '30.650000'], '请同时填写经纬度', '只填纬度应失败');
$assertInvalid(['long' => 'abc', 'lat' => '30.650000'], '经纬度格式错误', '非数字经度应失败');
$assertInvalid(['long' => '104.080000', 'lat' => 'abc'], '经纬度格式错误', '非数字纬度应失败');
$assertInvalid(['long' => '181', 'lat' => '30.650000'], '经度范围为 -180 到 180', '经度越界应失败');
$assertInvalid(['long' => '104.080000', 'lat' => '91'], '纬度范围为 -90 到 90', '纬度越界应失败');

echo "MerchantUpdateLocationValidateTest passed\n";
