<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use crmeb\services\huimaidan\StoreQrcodeWechatService;
use think\exception\ValidateException;

$service = new StoreQrcodeWechatService();
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

if ($service->resolveBinaryResponse($png) !== $png) {
    throw new RuntimeException('微信图片二进制返回应原样透出');
}

try {
    $service->resolveBinaryResponse(json_encode(['errcode' => 40169, 'errmsg' => 'invalid scene'], JSON_UNESCAPED_UNICODE));
    throw new RuntimeException('微信 JSON 错误返回应抛出异常');
} catch (ValidateException $e) {
    if (strpos($e->getMessage(), 'invalid scene') === false || strpos($e->getMessage(), '40169') === false) {
        throw $e;
    }
}

try {
    $service->resolveBinaryResponse(false);
    throw new RuntimeException('HTTP 调用失败应抛出异常');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '调用微信小程序码接口失败') {
        throw $e;
    }
}

try {
    $service->resolveBinaryResponse('not-image');
    throw new RuntimeException('非图片非 JSON 返回应抛出异常');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '微信小程序码接口返回异常') {
        throw $e;
    }
}

echo "StoreQrcodeWechatResponseTest passed\n";
