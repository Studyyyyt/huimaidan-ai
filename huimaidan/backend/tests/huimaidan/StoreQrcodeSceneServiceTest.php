<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\HuimaidanStoreQrcodeSceneService;
use think\exception\ValidateException;

$service = new HuimaidanStoreQrcodeSceneService();

$scene = $service->buildScene(1001, '8F2K9Q');
if ($scene !== 'm1001.e8F2K9Q') {
    throw new RuntimeException('scene 生成格式不正确: ' . $scene);
}

$parsed = $service->parseScene('m1001.e8F2K9Q');
if ($parsed !== ['mer_id' => 1001, 'entry_code' => '8F2K9Q']) {
    throw new RuntimeException('scene 解析结果不正确: ' . var_export($parsed, true));
}

try {
    $service->buildScene(0, '8F2K9Q');
    throw new RuntimeException('mer_id 非法时应抛出异常');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '商户ID无效') {
        throw $e;
    }
}

try {
    $service->buildScene(1001, '中文码');
    throw new RuntimeException('entry_code 非法时应抛出异常');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '入口码格式错误') {
        throw $e;
    }
}

try {
    $service->parseScene('id=1001&name=test');
    throw new RuntimeException('长 query 格式 scene 应被拒绝');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '二维码参数错误') {
        throw $e;
    }
}

echo "StoreQrcodeSceneServiceTest passed\n";
