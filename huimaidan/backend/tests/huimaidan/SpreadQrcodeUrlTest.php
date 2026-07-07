<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\HuimaidanStoreQrcodeImageService;
use app\common\repositories\user\UserRepository;

$reflection = new ReflectionClass(UserRepository::class);
if (!$reflection->hasMethod('normalizeRoutineQrcodeUrl')) {
    throw new RuntimeException('UserRepository 必须提供小程序推广二维码 URL 归一化方法');
}

$method = $reflection->getMethod('normalizeRoutineQrcodeUrl');
$method->setAccessible(true);
$repository = $reflection->newInstanceWithoutConstructor();

$localUrl = 'https://crmeb.local/uploads/routine/product/3a4ebeeea0a636c92e6b8c9f4580da49.jpg';
$expected = HuimaidanStoreQrcodeImageService::temporaryDevelopUrl('/uploads/routine/product/3a4ebeeea0a636c92e6b8c9f4580da49.jpg');
$actual = $method->invoke($repository, $localUrl);
if ($actual !== $expected) {
    throw new RuntimeException('crmeb.local 推广二维码地址必须归一成可访问开发域名: ' . $actual);
}

$remoteUrl = 'https://dev-huimaidan.yeeaf.net/uploads/routine/product/3a4ebeeea0a636c92e6b8c9f4580da49.jpg';
$actual = $method->invoke($repository, $remoteUrl);
if ($actual !== $remoteUrl) {
    throw new RuntimeException('非本地域名推广二维码地址不应被改写: ' . $actual);
}

echo "SpreadQrcodeUrlTest passed\n";
