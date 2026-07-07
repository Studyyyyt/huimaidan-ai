<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\HuimaidanStoreQrcodeRepository;

$reflection = new ReflectionClass(HuimaidanStoreQrcodeRepository::class);
$repository = $reflection->newInstanceWithoutConstructor();
$payload = $reflection->getMethod('payload');
$payload->setAccessible(true);

$record = [
    'id' => 7,
    'mer_id' => 1001,
    'entry_code' => '8F2K9Q',
    'scene_value' => 'm1001.e8F2K9Q',
    'scene_type' => 'payment_checkout',
    'page_path' => 'pages/scan-entry/index',
    'qr_image_url' => 'http://crmeb.local/uploads/huimaidan/store-qrcode/1001.png',
    'qr_image_path' => '/uploads/huimaidan/store-qrcode/1001.png',
    'status' => 1,
    'last_generate_status' => 1,
    'last_generate_error' => '',
    'generate_version' => 2,
    'refresh_count' => 1,
    'last_generated_at' => '2026-06-17 12:00:00',
    'last_access_at' => null,
    'branch_name_snapshot' => '万达店',
    'updated_at' => '2026-06-17 12:00:00',
];

$result = $payload->invoke($repository, $record, ['mer_name' => '示例商户']);
$expected = [
    'id' => 7,
    'mer_id' => 1001,
    'mer_name' => '示例商户',
    'branch_name_snapshot' => '万达店',
    'entry_code' => '8F2K9Q',
    'scene_value' => 'm1001.e8F2K9Q',
    'scene_type' => 'payment_checkout',
    'page_path' => 'pages/scan-entry/index',
    'status' => 1,
    'status_text' => '可用',
    'qr_image_url' => 'https://dev-huimaidan.yeeaf.net/uploads/huimaidan/store-qrcode/1001.png',
    'qr_image_path' => '/uploads/huimaidan/store-qrcode/1001.png',
    'last_generated_at' => '2026-06-17 12:00:00',
    'last_generate_status' => 1,
    'last_generate_status_text' => '生成成功',
    'last_generate_error' => '',
    'generate_version' => 2,
    'refresh_count' => 1,
    'last_access_at' => '',
    'is_using_last_success' => 0,
    'updated_at' => '2026-06-17 12:00:00',
];

if ($result !== $expected) {
    throw new RuntimeException('二维码详情响应结构不正确: ' . var_export($result, true));
}

$record['last_generate_status'] = 0;
$record['last_generate_error'] = 'invalid scene';
$failed = $payload->invoke($repository, $record, ['mer_name' => '示例商户']);
if ($failed['last_generate_status_text'] !== '生成失败' || $failed['is_using_last_success'] !== 1) {
    throw new RuntimeException('历史可用二维码失败态标识不正确: ' . var_export($failed, true));
}

echo "StoreQrcodeRepositoryPayloadTest passed\n";
