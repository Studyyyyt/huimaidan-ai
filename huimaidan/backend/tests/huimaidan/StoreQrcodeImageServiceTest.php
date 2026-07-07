<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\HuimaidanStoreQrcodeImageService;
use think\exception\ValidateException;

$root = sys_get_temp_dir() . '/huimaidan-store-qrcode-' . uniqid('', true);
$service = new HuimaidanStoreQrcodeImageService($root, 'https://example.com');
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

$saved = $service->save(1001, $png);
$expectedPath = '/uploads/huimaidan/store-qrcode/1001.png';
if ($saved['path'] !== $expectedPath) {
    throw new RuntimeException('二维码相对路径不正确: ' . var_export($saved, true));
}
if ($saved['url'] !== 'https://example.com' . $expectedPath) {
    throw new RuntimeException('二维码访问地址不正确: ' . var_export($saved, true));
}
if (!is_file($root . $expectedPath)) {
    throw new RuntimeException('二维码文件未写入预期位置');
}

$developService = new HuimaidanStoreQrcodeImageService($root);
$developSaved = $developService->save(1002, $png);
$developExpectedPath = '/uploads/huimaidan/store-qrcode/1002.png';
if ($developSaved['url'] !== 'https://dev-huimaidan.yeeaf.net' . $developExpectedPath) {
    throw new RuntimeException('开发环境二维码访问地址不正确: ' . var_export($developSaved, true));
}

try {
    $service->save(1001, 'not-image');
    throw new RuntimeException('无效图片二进制应抛出异常');
} catch (ValidateException $e) {
    if ($e->getMessage() !== '二维码图片内容无效') {
        throw $e;
    }
}

@unlink($root . $expectedPath);
@unlink($root . $developExpectedPath);
@rmdir($root . '/uploads/huimaidan/store-qrcode');
@rmdir($root . '/uploads/huimaidan');
@rmdir($root . '/uploads');
@rmdir($root);

echo "StoreQrcodeImageServiceTest passed\n";
